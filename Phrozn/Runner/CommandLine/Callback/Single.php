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
    Phrozn\Site\PieceOfSite as Site,
    Phrozn\Outputter;

/**
 * phrozn up command
 *
 * @category    Phrozn
 * @package     Phrozn\Runner\CommandLine
 * @author      Walter Dal Mut
 */
class Single
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
            $this->updateFile();
        } catch (\Exception $e) {
            $this->getOutputter()->stdout($e->getMessage(), self::STATUS_FAIL);
            $this->getOutputter()->stdout($this->getFooter(), Outputter::STATUS_CLEAR);
        }
    }

    private function updateFile()
    {
        list($file, $in, $out) = $this->getPaths();

        ob_start();
        $this->getOutputter()->stdout($this->getHeader(), Outputter::STATUS_CLEAR);
        $this->getOutputter()->stdout("Starting static file compilation.", Outputter::STATUS_CLEAR);

        $proceed = true;
        if (!is_dir($in)) {
            $this->getOutputter()->stdout("Source directory '{$in}' not found.", Outputter::STATUS_FAIL);
            $proceed = false;
        } else {
            $this->getOutputter()->stdout("Source directory located: {$in}");
        }
        if (!is_dir($out)) {
            $this->getOutputter()->stdout("Destination directory '{$out}' not found.", Outputter::STATUS_FAIL);
            $proceed = false;
        } else {
            $this->getOutputter()->stdout("Destination directory located: {$out}");
        }
        if (!is_file($file)) {
            $this->getOutputter()->stdout("Source file '{$file}' not found.", Outputter::STATUS_FAIL);
            $proceed = false;
        } else {
            $this->getOutputter()->stdout("Source file located: {$file}");
        }

        if ($proceed === false) {
            $this->getOutputter()->stdout($this->getFooter(), Outputter::STATUS_CLEAR);
            return;
        }

        $site = new Site($in, $out);
        $site->setSingleFile($file);
        $site
            ->setOutputter($this->getOutputter())
            ->compile();

        $this->getOutputter()->stdout($this->getFooter());

        ob_end_clean();
    }

    private function getPaths()
    {
        $in = $out = null;

        $file = $this->getParseResult()->command->args['file'];
        $in  = $this->getPathArgument('in');
        $out = $this->getPathArgument('out');

        if (strpos($in, '.phrozn') === false) {
            return array(
                $in . '/.phrozn/entries/' . $file,
                $in . '/.phrozn/',
                $out . '/'
            );
        } else {
            return array(
                $in . '/' . $file,
                $in . '/',
                $out . '/../'
            );
        }
    }
}

