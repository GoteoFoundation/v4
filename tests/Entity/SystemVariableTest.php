<?php

namespace App\Tests\Entity;

use App\Entity\SystemVariable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class SystemVariableTest extends KernelTestCase
{
    use ResetDatabase;

    public function testSerializesNulls()
    {
        $null = 'null:null';
        $this->assertEquals($null, SystemVariable::seralizeValue('null'));
        $this->assertEquals($null, SystemVariable::seralizeValue('NULL'));
        $this->assertEquals($null, SystemVariable::seralizeValue('none'));
    }

    public function testUnserializesNull()
    {
        $this->assertEquals(null, SystemVariable::unserializeValue('null:null'));
    }

    public function testSerializesBooleans()
    {
        $true = 'bool:true';
        $this->assertEquals($true, SystemVariable::seralizeValue(true));
        $this->assertEquals($true, SystemVariable::seralizeValue('true'));
        $this->assertEquals($true, SystemVariable::seralizeValue('TRUE'));
        $this->assertEquals($true, SystemVariable::seralizeValue('yes'));

        $false = 'bool:false';
        $this->assertEquals($false, SystemVariable::seralizeValue(false));
        $this->assertEquals($false, SystemVariable::seralizeValue('false'));
        $this->assertEquals($false, SystemVariable::seralizeValue('FALSE'));
        $this->assertEquals($false, SystemVariable::seralizeValue('no'));
    }

    public function testUnserializesBooleans()
    {
        $this->assertEquals(true, SystemVariable::unserializeValue('bool:true'));
        $this->assertEquals(false, SystemVariable::unserializeValue('bool:false'));
    }

    public function testSerializesNumerics()
    {
        $int = 'int:100';
        $this->assertEquals($int, SystemVariable::seralizeValue(100));
        $this->assertEquals($int, SystemVariable::seralizeValue('100'));

        $float = 'float:10';
        $this->assertEquals($float, SystemVariable::seralizeValue(10.0));
        $this->assertEquals($float, SystemVariable::seralizeValue('10.0'));
    }

    public function testUnserializesNumerics()
    {
        $this->assertEquals(100, SystemVariable::unserializeValue('int:100'));
        $this->assertEquals(10.0, SystemVariable::unserializeValue('float:10'));
    }

    public function testSerializesStrings()
    {
        $string = 'str:somevalue';
        $this->assertEquals($string, SystemVariable::seralizeValue('somevalue'));
    }

    public function testUnserializesStringsPreservingColons()
    {
        $colons = ':::';

        $this->assertEquals($colons, SystemVariable::unserializeValue(SystemVariable::seralizeValue($colons)));
    }
}
