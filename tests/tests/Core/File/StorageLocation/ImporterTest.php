<?php
namespace Concrete\Tests\Core\File\StorageLocation;
use Concrete\Core\Cache\CacheLocal;
use \Concrete\Core\File\StorageLocation\Type\Type;
use \Concrete\Core\File\StorageLocation\StorageLocation;
use \Concrete\Core\File\Importer;
use \Concrete\Core\Attribute\Type as AttributeType;
use \Concrete\Core\Attribute\Key\FileKey;
use \Concrete\Core\Attribute\Key\Category;

class ImporterTest extends \FileStorageTestCase {

    protected function setUp()
    {
        $this->tables = array_merge($this->tables, array(
            'Files',
            'FileVersions',
            'Users',
            'PermissionAccessEntityTypes',
            'FileAttributeValues',
            'AttributeKeyCategories',
            'AttributeTypes',
            'Config',
            'AttributeKeys',
            'AttributeValues',
            'atNumber',
            'FileVersionLog'
        ));
        parent::setUp();
        define('UPLOAD_FILE_EXTENSIONS_ALLOWED', '*.txt;*.jpeg;*.png');

        $category = Category::add('file');
        $number = AttributeType::add('number', 'Number');
        FileKey::add($number, array('akHandle' => 'width', 'akName' => 'Width'));
        FileKey::add($number, array('akHandle' => 'height', 'akName' => 'Height'));
        CacheLocal::flush();
    }

    protected function cleanup()
    {
        parent::cleanup();
        if (file_exists(dirname(__FILE__) . '/test.txt')) {
            unlink(dirname(__FILE__) . '/test.txt');
        }
        if (file_exists(dirname(__FILE__) . '/test.invalid')) {
            unlink(dirname(__FILE__) . '/test.invalid');
        }
    }

    public function testFileNotFound()
    {
        $fi = new Importer();
        $r = $fi->import('foo.txt', 'foo.txt');
        $this->assertEquals($r, Importer::E_FILE_INVALID);
    }

    public function testFileInvalidExtension()
    {
        $file = dirname(__FILE__) . '/test.invalid';
        touch($file);
        $fi = new Importer();
        $r = $fi->import($file, 'test.invalid');
        $this->assertEquals($r, Importer::E_FILE_INVALID_EXTENSION);
    }

    public function testFileInvalidStorageLocation()
    {
        $file = dirname(__FILE__) . '/test.txt';
        touch($file);
        $fi = new Importer();
        $r = $fi->import($file, 'test.txt');
        $this->assertEquals($r, Importer::E_FILE_INVALID_STORAGE_LOCATION);
    }

    public function testFileValid()
    {
        // create the default storage location first.
        mkdir($this->getStorageDirectory());
        $this->getStorageLocation();

        $file = dirname(__FILE__) . '/test.txt';
        touch($file);
        $fi = new Importer();
        $r = $fi->import($file, 'test.txt');

        $this->assertInstanceOf('\Concrete\Core\File\Version', $r);
        $this->assertEquals($r->getFileVersionID(), 1);
        $this->assertEquals($r->getFileID(), 1);
        $this->assertEquals('test.txt', $r->getFilename());
        $fo = $r->getFile();
        $fsl = $fo->getFileStorageLocationObject();
        $this->assertEquals(true, $fsl->isDefault());
        $this->assertInstanceOf('\Concrete\Core\File\StorageLocation\StorageLocation', $fsl);
        $apr = str_split($r->getPrefix(), 4);

        $this->assertEquals(REL_DIR_FILES_UPLOADED . '/' . $apr[0] . '/' . $apr[1] . '/' . $apr[2] . '/test.txt',
            $r->getRelativePath()
        );

    }

    public function testImageImportSize()
    {
        // create the default storage location first.
        mkdir($this->getStorageDirectory());
        $this->getStorageLocation();

        $file = DIR_BASE . '/concrete/images/logo.png';
        $fi = new Importer();
        $fo = $fi->import($file, 'My Logo.png');
        $type = $fo->getTypeObject();
        $this->assertEquals(\Concrete\Core\File\Type\Type::T_IMAGE, $type->getGenericType());

        $this->assertEquals(113, $fo->getAttribute('width'));
        $this->assertEquals(113, $fo->getAttribute('height'));

    }

    public function testImageImport()
    {
        // create the default storage location first.
        mkdir($this->getStorageDirectory());
        $this->getStorageLocation();

        $file = DIR_BASE . '/concrete/images/logo.png';
        $fi = new Importer();
        $fo = $fi->import($file, 'My Logo.png');
        $type = $fo->getTypeObject();
        
    }

    public function testFileReplace()
    {

        // create the default storage location first.
        mkdir($this->getStorageDirectory());
        $this->getStorageLocation();

        $file = dirname(__FILE__) . '/test.txt';
        touch($file);
        $fi = new Importer();
        $fo = $fi->import($file, 'test.txt');
        $fo = $fo->getFile();

        $sample = dirname(__FILE__) . '/fixtures/sample.txt';
        $r = $fi->import($sample, 'sample.txt', $fo);

        $this->assertInstanceOf('\Concrete\Core\File\Version', $r);
        $this->assertEquals(2, $r->getFileVersionID());
        $this->assertEquals('sample.txt', $r->getFilename());
        $apr = str_split($r->getPrefix(), 4);
        $this->assertEquals(BASE_URL . '/application/files/' . $apr[0] . '/' . $apr[1] . '/' . $apr[2] . '/sample.txt',
            $r->getURL()
        );
    }

}
 