<?php

namespace Illuminatech\ArrayFactory\Test;

use Illuminatech\ArrayFactory\Definition;

class DefinitionTest extends TestCase
{
    public function testVarExport()
    {
        $definition = new Definition([
            '__class' => __CLASS__,
        ]);

        $exportedString = var_export($definition, true);

        $restoredDefinition = eval("return {$exportedString};");

        $this->assertTrue($restoredDefinition instanceof Definition);
        $this->assertSame($definition->definition, $restoredDefinition->definition);
    }
}
