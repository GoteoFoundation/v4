<?php

namespace App\Tests\Entity;

use App\Entity\SystemVar;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class SystemVarTest extends KernelTestCase
{
    use ResetDatabase;

    public function testSerializesNulls()
    {
        $null = "null:null";
        $this->assertEquals($null, SystemVar::seralizeValue("null"));
        $this->assertEquals($null, SystemVar::seralizeValue("NULL"));
        $this->assertEquals($null, SystemVar::seralizeValue("none"));
    }

    public function testUnserializesNull()
    {
        $this->assertEquals(null, SystemVar::unserializeValue("null:null"));
    }

    public function testSerializesBooleans()
    {
        $true = "bool:true";
        $this->assertEquals($true, SystemVar::seralizeValue(true));
        $this->assertEquals($true, SystemVar::seralizeValue("true"));
        $this->assertEquals($true, SystemVar::seralizeValue("TRUE"));
        $this->assertEquals($true, SystemVar::seralizeValue("yes"));

        $false = "bool:false";
        $this->assertEquals($false, SystemVar::seralizeValue(false));
        $this->assertEquals($false, SystemVar::seralizeValue("false"));
        $this->assertEquals($false, SystemVar::seralizeValue("FALSE"));
        $this->assertEquals($false, SystemVar::seralizeValue("no"));
    }

    public function testUnserializesBooleans()
    {
        $this->assertEquals(true, SystemVar::unserializeValue("bool:true"));
        $this->assertEquals(false, SystemVar::unserializeValue("bool:false"));
    }

    public function testSerializesNumerics()
    {
        $int = "int:100";
        $this->assertEquals($int, SystemVar::seralizeValue(100));
        $this->assertEquals($int, SystemVar::seralizeValue("100"));

        $float = "float:10";
        $this->assertEquals($float, SystemVar::seralizeValue(10.0));
        $this->assertEquals($float, SystemVar::seralizeValue("10.0"));
    }

    public function testUnserializesNumerics()
    {
        $this->assertEquals(100, SystemVar::unserializeValue("int:100"));
        $this->assertEquals(10.0, SystemVar::unserializeValue("float:10"));
    }

    public function testSerializesStrings()
    {
        $string = "str:somevalue";
        $this->assertEquals($string, SystemVar::seralizeValue("somevalue"));
    }

    public function testUnserializesStringsPreservingColons()
    {
        $colons = ":::";

        $this->assertEquals($colons, SystemVar::unserializeValue(SystemVar::seralizeValue($colons)));
    }
}
