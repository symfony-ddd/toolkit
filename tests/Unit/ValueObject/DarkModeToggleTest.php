<?php

namespace SymfonyDDD\CoreBundle\Tests\Unit\ValueObject;

use PHPUnit\Framework\TestCase;
use SymfonyDDD\CoreBundle\Tests\Unit\ValueObject\Mock\DarkModeToggle;

class DarkModeToggleTest extends TestCase
{
    public function testStaticConstructors(): void
    {
        $enabled = DarkModeToggle::enabled();
        $disabled = DarkModeToggle::disabled();
        $fromBoolTrue = DarkModeToggle::fromBoolean(true);
        $fromBoolFalse = DarkModeToggle::fromBoolean(false);

        assert($enabled->isEnabled());
        assert($disabled->isDisabled());
        assert($fromBoolTrue->isEnabled());
        assert($fromBoolFalse->isDisabled());
    }

    public function testEqualityComparison(): void
    {
        $enabled1 = DarkModeToggle::enabled();
        $enabled2 = DarkModeToggle::enabled();
        $disabled1 = DarkModeToggle::disabled();
        $disabled2 = DarkModeToggle::disabled();

        assert($enabled1->eq($enabled2));
        assert($disabled1->eq($disabled2));
        assert(!$enabled1->eq($disabled1));
        assert(!$disabled1->eq($enabled1));
    }

    public function testInequalityComparison(): void
    {
        $enabled = DarkModeToggle::enabled();
        $disabled = DarkModeToggle::disabled();
        $anotherEnabled = DarkModeToggle::enabled();

        assert($enabled->neq($disabled));
        assert($disabled->neq($enabled));
        assert(!$enabled->neq($anotherEnabled));
    }

    public function testBooleanStateChecks(): void
    {
        $enabled = DarkModeToggle::enabled();
        $disabled = DarkModeToggle::disabled();

        assert($enabled->isEnabled());
        assert(!$enabled->isDisabled());
        assert($enabled->isEnabled());
        assert(!$enabled->isDisabled());

        assert(!$disabled->isEnabled());
        assert($disabled->isDisabled());
        assert(!$disabled->isEnabled());
        assert($disabled->isDisabled());
    }

    public function testToggleOperation(): void
    {
        $enabled = DarkModeToggle::enabled();
        $disabled = DarkModeToggle::disabled();

        $toggledEnabled = $enabled->toggle();
        $toggledDisabled = $disabled->toggle();

        assert($toggledEnabled->isDisabled());
        assert($toggledDisabled->isEnabled());

        // Original objects should remain unchanged
        assert($enabled->isEnabled());
        assert($disabled->isDisabled());
    }

    public function testLogicalOperations(): void
    {
        $enabled = DarkModeToggle::enabled();
        $disabled = DarkModeToggle::disabled();
        $anotherEnabled = DarkModeToggle::enabled();
        $anotherDisabled = DarkModeToggle::disabled();

        // AND operations
        assert($enabled->and($anotherEnabled) === true);
        assert($enabled->and($disabled) === false);
        assert($disabled->and($enabled) === false);
        assert($disabled->and($anotherDisabled) === false);

        // OR operations
        assert($enabled->or($anotherEnabled) === true);
        assert($enabled->or($disabled) === true);
        assert($disabled->or($enabled) === true);
        assert($disabled->or($anotherDisabled) === false);

        // XOR operations
        assert($enabled->xor($anotherEnabled) === false);
        assert($enabled->xor($disabled) === true);
        assert($disabled->xor($enabled) === true);
        assert($disabled->xor($anotherDisabled) === false);

        // NOT operations
        assert($enabled->not() === false);
        assert($disabled->not() === true);
    }

    public function testEnableDisableMethods(): void
    {
        $toggle = DarkModeToggle::disabled();

        $enabledToggle = $toggle->enable();
        $disabledToggle = $toggle->disable();

        assert($enabledToggle->isEnabled());
        assert($disabledToggle->isDisabled());

        // Original should remain unchanged
        assert($toggle->isDisabled());
    }

    public function testStringRepresentation(): void
    {
        $enabled = DarkModeToggle::enabled();
        $disabled = DarkModeToggle::disabled();

        assert((string) $enabled === 'true');
        assert((string) $disabled === 'false');
        assert($enabled->__toString() === 'true');
        assert($disabled->__toString() === 'false');
    }

    public function testValueProperty(): void
    {
        $enabled = DarkModeToggle::enabled();
        $disabled = DarkModeToggle::disabled();

        assert($enabled->value === true);
        assert($disabled->value === false);
    }

    public function testImmutability(): void
    {
        $original = DarkModeToggle::enabled();
        $toggled = $original->toggle();
        $enabled = $original->enable();
        $disabled = $original->disable();

        // All operations should return new instances
        assert($original !== $toggled);
        assert($original !== $enabled);
        assert($original !== $disabled);

        // Original should remain unchanged
        assert($original->isEnabled());
    }
}
