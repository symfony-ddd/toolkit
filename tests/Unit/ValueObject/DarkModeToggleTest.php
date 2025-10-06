<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\Tests\Unit\ValueObject;

use PHPUnit\Framework\TestCase;
use SymfonyDDD\ToolkitBundle\Tests\Unit\ValueObject\Mock\DarkModeToggle;

class DarkModeToggleTest extends TestCase
{
    public function testStaticConstructors(): void
    {
        $enabled = DarkModeToggle::enabled();
        $disabled = DarkModeToggle::disabled();
        $fromBoolTrue = DarkModeToggle::fromBoolean(true);
        $fromBoolFalse = DarkModeToggle::fromBoolean(false);

        $this->assertTrue($enabled->isEnabled());
        $this->assertTrue($disabled->isDisabled());
        $this->assertTrue($fromBoolTrue->isEnabled());
        $this->assertTrue($fromBoolFalse->isDisabled());
    }

    public function testEqualityComparison(): void
    {
        $enabled1 = DarkModeToggle::enabled();
        $enabled2 = DarkModeToggle::enabled();
        $disabled1 = DarkModeToggle::disabled();
        $disabled2 = DarkModeToggle::disabled();

        $this->assertTrue($enabled1->eq($enabled2));
        $this->assertTrue($disabled1->eq($disabled2));
        $this->assertTrue(!$enabled1->eq($disabled1));
        $this->assertTrue(!$disabled1->eq($enabled1));
    }

    public function testInequalityComparison(): void
    {
        $enabled = DarkModeToggle::enabled();
        $disabled = DarkModeToggle::disabled();
        $anotherEnabled = DarkModeToggle::enabled();

        $this->assertTrue($enabled->neq($disabled));
        $this->assertTrue($disabled->neq($enabled));
        $this->assertTrue(!$enabled->neq($anotherEnabled));
    }

    public function testBooleanStateChecks(): void
    {
        $enabled = DarkModeToggle::enabled();
        $disabled = DarkModeToggle::disabled();

        $this->assertTrue($enabled->isEnabled());
        $this->assertTrue(!$enabled->isDisabled());

        $this->assertTrue(!$disabled->isEnabled());
        $this->assertTrue($disabled->isDisabled());
    }

    public function testToggleOperation(): void
    {
        $enabled = DarkModeToggle::enabled();
        $disabled = DarkModeToggle::disabled();

        $toggledEnabled = $enabled->toggle();
        $toggledDisabled = $disabled->toggle();

        $this->assertTrue($toggledEnabled->isDisabled());
        $this->assertTrue($toggledDisabled->isEnabled());

        $this->assertTrue($enabled->isEnabled());
        $this->assertTrue($disabled->isDisabled());
    }

    public function testLogicalOperations(): void
    {
        $enabled = DarkModeToggle::enabled();
        $disabled = DarkModeToggle::disabled();
        $anotherEnabled = DarkModeToggle::enabled();
        $anotherDisabled = DarkModeToggle::disabled();

        // AND operations
        $this->assertTrue($enabled->and($anotherEnabled) === true);
        $this->assertTrue($enabled->and($disabled) === false);
        $this->assertTrue($disabled->and($enabled) === false);
        $this->assertTrue($disabled->and($anotherDisabled) === false);

        // OR operations
        $this->assertTrue($enabled->or($anotherEnabled) === true);
        $this->assertTrue($enabled->or($disabled) === true);
        $this->assertTrue($disabled->or($enabled) === true);
        $this->assertTrue($disabled->or($anotherDisabled) === false);

        // NOT operations
        $this->assertTrue($enabled->not() === false);
        $this->assertTrue($disabled->not() === true);
    }

    public function testValueProperty(): void
    {
        $enabled = DarkModeToggle::enabled();
        $disabled = DarkModeToggle::disabled();

        $this->assertTrue($enabled->value === true);
        $this->assertTrue($disabled->value === false);
    }

    public function testImmutability(): void
    {
        $original = DarkModeToggle::enabled();
        $toggled = $original->toggle();

        // All operations should return new instances
        $this->assertTrue($original !== $toggled);

        // Original should remain unchanged
        $this->assertTrue($original->isEnabled());
    }
}
