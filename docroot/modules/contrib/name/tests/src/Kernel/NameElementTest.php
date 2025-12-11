<?php

declare(strict_types=1);

namespace Drupal\Tests\name\Kernel;

use Drupal\Core\Render\ElementInfoManagerInterface;
use Drupal\name\Element\Name;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the Name element integration.
 *
 * @group name
 * @coversDefaultClass \Drupal\name\Element\Name
 */
class NameElementTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'name',
    'field',
    'system',
    'user',
  ];

  /**
   * The element info manager.
   *
   * @var \Drupal\Core\Render\ElementInfoManagerInterface
   */
  protected ElementInfoManagerInterface $elementInfoManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(self::$modules);

    $this->elementInfoManager = $this->container->get('plugin.manager.element_info');
  }

  /**
   * Tests that the Name element is properly registered.
   *
   * @covers ::getInfo
   */
  public function testElementRegistration(): void {
    $info = $this->elementInfoManager->getInfo('name');

    $this->assertNotEmpty($info);
    $this->assertTrue($info['#input']);
    $this->assertContains('name_element_expand', $info['#process']);
    $this->assertContains([Name::class, 'preRender'], $info['#pre_render']);
    $this->assertContains('name_element_validate', $info['#element_validate']);
    $this->assertContains('form_element', $info['#theme_wrappers']);
  }

  /**
   * Tests the preRender method with default layout.
   *
   * @covers ::preRender
   */
  public function testPreRenderWithDefaultLayout(): void {
    $element = [
      'title' => [
        '#type' => 'select',
        '#title' => 'Title',
        '#options' => ['Dr.' => 'Dr.', 'Mr.' => 'Mr.'],
      ],
      'given' => [
        '#type' => 'textfield',
        '#title' => 'Given Name',
      ],
      'family' => [
        '#type' => 'textfield',
        '#title' => 'Family Name',
      ],
      '#widget_layout' => 'stacked',
    ];

    $result = Name::preRender($element);

    // Check that the element structure is correct.
    $this->assertArrayHasKey('_name', $result);
    $this->assertArrayHasKey('#prefix', $result['_name']);
    $this->assertArrayHasKey('#suffix', $result['_name']);

    // Check that the wrapper div is created.
    $this->assertStringContainsString('<div', $result['_name']['#prefix']);
    $this->assertStringContainsString('</div>', $result['_name']['#suffix']);

    // Check that components are moved to _name.
    $this->assertArrayHasKey('title', $result['_name']);
    $this->assertArrayHasKey('given', $result['_name']);
    $this->assertArrayHasKey('family', $result['_name']);

    // Check that original components are removed.
    $this->assertArrayNotHasKey('title', $result);
    $this->assertArrayNotHasKey('given', $result);
    $this->assertArrayNotHasKey('family', $result);
  }

  /**
   * Tests the preRender method with inline layout.
   *
   * @covers ::preRender
   */
  public function testPreRenderWithInlineLayout(): void {
    $element = [
      'title' => [
        '#type' => 'select',
        '#title' => 'Title',
      ],
      'given' => [
        '#type' => 'textfield',
        '#title' => 'Given Name',
      ],
      'family' => [
        '#type' => 'textfield',
        '#title' => 'Family Name',
      ],
      '#widget_layout' => 'inline',
    ];

    $result = Name::preRender($element);

    // Check that the element structure is correct.
    $this->assertArrayHasKey('_name', $result);
    $this->assertArrayHasKey('#prefix', $result['_name']);
    $this->assertArrayHasKey('#suffix', $result['_name']);

    // Check that the wrapper div is created.
    $this->assertStringContainsString('<div', $result['_name']['#prefix']);
    $this->assertStringContainsString('</div>', $result['_name']['#suffix']);

    // Check that components are moved to _name.
    $this->assertArrayHasKey('title', $result['_name']);
    $this->assertArrayHasKey('given', $result['_name']);
    $this->assertArrayHasKey('family', $result['_name']);
  }

  /**
   * Tests the preRender method with custom component layout.
   *
   * @covers ::preRender
   */
  public function testPreRenderWithCustomComponentLayout(): void {
    $element = [
      'title' => [
        '#type' => 'select',
        '#title' => 'Title',
      ],
      'given' => [
        '#type' => 'textfield',
        '#title' => 'Given Name',
      ],
      'family' => [
        '#type' => 'textfield',
        '#title' => 'Family Name',
      ],
      '#component_layout' => 'custom_layout',
      '#widget_layout' => 'stacked',
    ];

    $result = Name::preRender($element);

    // Check that the element structure is correct.
    $this->assertArrayHasKey('_name', $result);
    $this->assertArrayHasKey('title', $result['_name']);
    $this->assertArrayHasKey('given', $result['_name']);
    $this->assertArrayHasKey('family', $result['_name']);
  }

  /**
   * Tests the preRender method with library attachment.
   *
   * @covers ::preRender
   */
  public function testPreRenderWithLibraryAttachment(): void {
    $element = [
      'title' => [
        '#type' => 'select',
        '#title' => 'Title',
      ],
      'given' => [
        '#type' => 'textfield',
        '#title' => 'Given Name',
      ],
      '#widget_layout' => 'inline',
    ];

    $result = Name::preRender($element);

    // Check that library is attached for inline layout.
    $this->assertArrayHasKey('#attached', $result);
    $this->assertArrayHasKey('library', $result['#attached']);
    $this->assertContains('name/widget.inline', $result['#attached']['library']);
  }

  /**
   * Tests the preRender method with all name components.
   *
   * @covers ::preRender
   */
  public function testPreRenderWithAllComponents(): void {
    $element = [
      'title' => [
        '#type' => 'select',
        '#title' => 'Title',
      ],
      'given' => [
        '#type' => 'textfield',
        '#title' => 'Given Name',
      ],
      'middle' => [
        '#type' => 'textfield',
        '#title' => 'Middle Name',
      ],
      'family' => [
        '#type' => 'textfield',
        '#title' => 'Family Name',
      ],
      'generational' => [
        '#type' => 'select',
        '#title' => 'Generational',
      ],
      'credentials' => [
        '#type' => 'textfield',
        '#title' => 'Credentials',
      ],
      '#widget_layout' => 'stacked',
    ];

    $result = Name::preRender($element);

    // Check that all components are moved to _name.
    $this->assertArrayHasKey('title', $result['_name']);
    $this->assertArrayHasKey('given', $result['_name']);
    $this->assertArrayHasKey('middle', $result['_name']);
    $this->assertArrayHasKey('family', $result['_name']);
    $this->assertArrayHasKey('generational', $result['_name']);
    $this->assertArrayHasKey('credentials', $result['_name']);

    // Check that original components are removed.
    $this->assertArrayNotHasKey('title', $result);
    $this->assertArrayNotHasKey('given', $result);
    $this->assertArrayNotHasKey('middle', $result);
    $this->assertArrayNotHasKey('family', $result);
    $this->assertArrayNotHasKey('generational', $result);
    $this->assertArrayNotHasKey('credentials', $result);
  }

  /**
   * Tests the preRender method with empty element.
   *
   * @covers ::preRender
   */
  public function testPreRenderWithEmptyElement(): void {
    $element = [
      '#widget_layout' => 'stacked',
    ];

    $result = Name::preRender($element);

    // Check that the element structure is correct even with no components.
    $this->assertArrayHasKey('_name', $result);
    $this->assertArrayHasKey('#prefix', $result['_name']);
    $this->assertArrayHasKey('#suffix', $result['_name']);

    // Check that the wrapper div is created.
    $this->assertStringContainsString('<div', $result['_name']['#prefix']);
    $this->assertStringContainsString('</div>', $result['_name']['#suffix']);
  }

  /**
   * Tests the preRender method with invalid widget layout.
   *
   * @covers ::preRender
   */
  public function testPreRenderWithInvalidWidgetLayout(): void {
    $element = [
      'title' => [
        '#type' => 'select',
        '#title' => 'Title',
      ],
      'given' => [
        '#type' => 'textfield',
        '#title' => 'Given Name',
      ],
      '#widget_layout' => 'invalid_layout',
    ];

    $result = Name::preRender($element);

    // Should fall back to stacked layout.
    $this->assertArrayHasKey('_name', $result);
    $this->assertArrayHasKey('#prefix', $result['_name']);
    $this->assertArrayHasKey('#suffix', $result['_name']);

    // Check that the wrapper div is created.
    $this->assertStringContainsString('<div', $result['_name']['#prefix']);
    $this->assertStringContainsString('</div>', $result['_name']['#suffix']);
  }

  /**
   * Tests that the element implements TrustedCallbackInterface.
   */
  public function testTrustedCallbackInterface(): void {
    $callbacks = Name::trustedCallbacks();
    $this->assertIsArray($callbacks);
    $this->assertContains('preRender', $callbacks);
  }

  /**
   * Tests that the element extends FormElementBase.
   */
  public function testFormElementBaseInheritance(): void {
    $info = $this->elementInfoManager->getInfo('name');
    $this->assertNotEmpty($info);
    $this->assertTrue($info['#input']);
  }

}
