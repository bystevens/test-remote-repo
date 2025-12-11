<?php

declare(strict_types=1);

namespace Drupal\Tests\name\Unit;

use Drupal\Core\Form\FormStateInterface;
use Drupal\name\Element\Name;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the Name element.
 *
 * @group name
 * @coversDefaultClass \Drupal\name\Element\Name
 */
class NameElementTest extends UnitTestCase {

  /**
   * Tests the trustedCallbacks method.
   *
   * @covers ::trustedCallbacks
   */
  public function testTrustedCallbacks(): void {
    $callbacks = Name::trustedCallbacks();
    $this->assertIsArray($callbacks);
    $this->assertContains('preRender', $callbacks);
    $this->assertCount(1, $callbacks);
  }

  /**
   * Tests the valueCallback method with FALSE input.
   *
   * @covers ::valueCallback
   */
  public function testValueCallbackWithFalseInput(): void {
    $element = [
      '#default_value' => [
        'title' => 'Dr.',
        'given' => 'John',
        'family' => 'Doe',
      ],
    ];
    $form_state = $this->createMock(FormStateInterface::class);

    $result = Name::valueCallback($element, FALSE, $form_state);

    $expected = [
      'title' => 'Dr.',
      'given' => 'John',
      'family' => 'Doe',
      'middle' => '',
      'generational' => '',
      'credentials' => '',
    ];
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests the valueCallback method with valid input.
   *
   * @covers ::valueCallback
   */
  public function testValueCallbackWithValidInput(): void {
    $element = [];
    $input = [
      'title' => 'Dr.',
      'given' => 'John',
      'middle' => 'Michael',
      'family' => 'Doe',
      'generational' => 'Jr.',
      'credentials' => 'PhD',
    ];
    $form_state = $this->createMock(FormStateInterface::class);

    $result = Name::valueCallback($element, $input, $form_state);

    $expected = [
      'title' => 'Dr.',
      'given' => 'John',
      'middle' => 'Michael',
      'family' => 'Doe',
      'generational' => 'Jr.',
      'credentials' => 'PhD',
    ];
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests the valueCallback method with scalar input conversion.
   *
   * @covers ::valueCallback
   */
  public function testValueCallbackWithScalarInputConversion(): void {
    $element = [];
    $input = [
      'title' => 123,
      'given' => TRUE,
      'family' => 456.78,
    ];
    $form_state = $this->createMock(FormStateInterface::class);

    $result = Name::valueCallback($element, $input, $form_state);

    $expected = [
      'title' => '123',
      'given' => '1',
      'family' => '456.78',
      'middle' => '',
      'generational' => '',
      'credentials' => '',
    ];
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests the valueCallback method with invalid input keys.
   *
   * @covers ::valueCallback
   */
  public function testValueCallbackWithInvalidInputKeys(): void {
    $element = [];
    $input = [
      'title' => 'Dr.',
      'invalid_key' => 'should_be_ignored',
      'given' => 'John',
      'another_invalid' => 'also_ignored',
    ];
    $form_state = $this->createMock(FormStateInterface::class);

    $result = Name::valueCallback($element, $input, $form_state);

    $expected = [
      'title' => 'Dr.',
      'given' => 'John',
      'middle' => '',
      'family' => '',
      'generational' => '',
      'credentials' => '',
    ];
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests the valueCallback method with nested array input.
   *
   * @covers ::valueCallback
   */
  public function testValueCallbackWithNestedArrayInput(): void {
    $element = [];
    $input = [
      'title' => ['nested' => 'array'],
      'given' => 'John',
      'family' => ['should' => 'be_ignored'],
    ];
    $form_state = $this->createMock(FormStateInterface::class);

    $result = Name::valueCallback($element, $input, $form_state);

    $expected = [
      'title' => '',
      'given' => 'John',
      'family' => '',
      'middle' => '',
      'generational' => '',
      'credentials' => '',
    ];
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests the valueCallback method with empty input.
   *
   * @covers ::valueCallback
   */
  public function testValueCallbackWithEmptyInput(): void {
    $element = [];
    $input = [];
    $form_state = $this->createMock(FormStateInterface::class);

    $result = Name::valueCallback($element, $input, $form_state);

    $expected = [
      'title' => '',
      'given' => '',
      'middle' => '',
      'family' => '',
      'generational' => '',
      'credentials' => '',
    ];
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests the valueCallback method with NULL input.
   *
   * @covers ::valueCallback
   */
  public function testValueCallbackWithNullInput(): void {
    $element = [];
    $input = NULL;
    $form_state = $this->createMock(FormStateInterface::class);

    $result = Name::valueCallback($element, $input, $form_state);

    $expected = [
      'title' => '',
      'given' => '',
      'middle' => '',
      'family' => '',
      'generational' => '',
      'credentials' => '',
    ];
    $this->assertEquals($expected, $result);
  }

}
