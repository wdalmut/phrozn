<?php
/**
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *          http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category    Phrozn
 * @package     Phrozn\Runner\CommandLine
 * @author      Victor Farazdagi
 * @license     http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Phrozn\Runner\CommandLine\Callback;
use Phrozn\Outputter\Console\Color,
    Symfony\Component\Yaml\Yaml,
    Phrozn\Runner\CommandLine,
    Phrozn\Outputter;

/**
 * phrozn clobber command
 *
 * @category    Phrozn
 * @package     Phrozn\Runner\CommandLine
 * @author      Victor Farazdagi
 */
class Clobber
    extends Base
    implements CommandLine\Callback
{
    /**
     * Executes the callback action
     *
     * @return string
     */
    public function execute()
    {
        try {
            $this->purgeProject();
        } catch (\Exception $e) {
            $this->getOutputter()->stdout($e->getMessage(), Outputter::STATUS_FAIL);
            $this->getOutputter()->stdout($this->getFooter(), Outputter::STATUS_CLEAR);
        }
    }

    private function purgeProject()
    {
        $path = isset($this->getParseResult()->command->args['path'])
               ? $this->getParseResult()->command->args['path'] : \getcwd() . '/.phrozn';

        if (!$this->isAbsolute($path)) { // not an absolute path
            $path = realpath(\getcwd() . '/./' . $path);
        }

        $config = $this->getConfig();

        $this->getOutputter()->stdout($this->getHeader(), Outputter::STATUS_CLEAR);
        $this->getOutputter()->stdout("Purging project data..");
        $this->getOutputter()->stdout("Located project folder: {$path}");
        $this->getOutputter()->stdout("Project folder is to be removed.", Outputter::STATUS_WARN);
        $this->getOutputter()->stdout("This operation %rCAN NOT%n be undone." . PHP_EOL, Outputter::STATUS_WARN);

        if (!$path || is_dir($path) === false) {
            throw new \RuntimeException("No project found at \"{$path}\"");
        }

        if ($this->readLine() === 'yes') {
            $this->removePath($path);
            rmdir($path);
            $this->getOutputter()->stdout(" Remove project folder: {$path}", Outputter::STATUS_DELETED);
        } else {
            $this->getOutputter()->stdout(" Aborted..", Outputter::STATUS_FAIL);
        }
        $this->getOutputter()->stdout($this->getFooter(), Outputter::STATUS_CLEAR);
    }

    private function removePath($path)
    {
        $directory = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);

        foreach ($directory as $file) {
            if ($file->isDir()) {
                $this->removePath($file->getPathname());
                rmdir($file);
                $this->getOutputter()->stdout(" Remove folder: {$file->getPathname()}", Outputter::STATUS_DELETED);
            } else {
                unlink($file->getPathname());
                $this->getOutputter()->stdout(" Remove file: {$file->getPathname()}", Outputter::STATUS_DELETED);
            }
        }
    }
}
