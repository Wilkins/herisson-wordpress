<?php
/**
 * CsvTest
 *
 * PHP Version 5.3
 *
 * @category Test
 * @package  Herisson
 * @author   Thibault Taillandier <thibault@taillandier.name>
 * @license  http://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 * @link     None
 */

namespace Herisson\Format;

require_once __DIR__."/../../Env.php";

/**
 * Class: CsvTest
 * 
 * Test HerissonEncryption class
 *
 * @category Test
 * @package  Herisson
 * @author   Thibault Taillandier <thibault@taillandier.name>
 * @license  http://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 * @link     None
 * @see      PHPUnit_Framework_TestCase
 */
class CsvTest extends Base
{


    /**
     * Configuration
     *
     * Create sample data, and Encryption object
     *
     * @return void
     */
    protected function setUp()
    {
        $this->format = new Csv();
    }

    /**
     * Test size of the export method
     * 
     * @return void
     */
    public function testExport()
    {
        ob_start();
        $this->format->export($this->_getBookmarks());
        $output = ob_get_clean();
        $this->assertCount(22, explode("\n", $output));
        $this->assertRegexp('/fdn/', $output);
        
    }


}

