<?php

declare(strict_types=1);

namespace Drupal\Tests\name\Unit\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\Exception\UndefinedLinkTemplateException;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\name\Plugin\Field\FieldFormatter\NameFormatter;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__ . '/../../../../../../name.module';

/**
 * Tests the Name field formatter plugin.
 *
 * @group name
 * @coversDefaultClass \Drupal\name\Plugin\Field\FieldFormatter\NameFormatter
 * @uses \Drupal\name\Plugin\Field\FieldFormatter\NameFormatter
 */
class NameFormatterTest extends UnitTestCase {

  /**
   * The plugin.
   *
   * @var \Drupal\name\Plugin\Field\FieldFormatter\NameFormatter
   */
  protected $plugin;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManager
   */
  protected $entityFieldManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The name formatter.
   *
   * @var \Drupal\name\NameFormatter
   */
  protected $nameFormatter;

  /**
   * The name parser.
   *
   * @var \Drupal\name\NameFormatParser
   */
  protected $nameParser;

  /**
   * The name generator.
   *
   * @var \Drupal\name\NameGeneratorInterface
   */
  protected $nameGenerator;

  /**
   * The field definition.
   *
   * @var \Drupal\Core\Field\FieldDefinitionInterface
   */
  protected $fieldDefinition;

  /**
   * The name format storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nameFormatStorage;

  /**
   * The name list format storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nameListFormatStorage;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();

    $string_translation = $this->getStringTranslationStub();
    $container->set('string_translation', $string_translation);

    $this->entityFieldManager = $this->createMock('Drupal\Core\Entity\EntityFieldManager');
    $container->set('entity_field.manager', $this->entityFieldManager);

    $this->entityTypeManager = $this->createMock('Drupal\Core\Entity\EntityTypeManagerInterface');
    $container->set('entity_type.manager', $this->entityTypeManager);

    $this->renderer = $this->createMock('Drupal\Core\Render\RendererInterface');
    $container->set('renderer', $this->renderer);

    $this->nameFormatter = $this->createMock('Drupal\name\NameFormatter');
    $container->set('name.formatter', $this->nameFormatter);

    $this->nameParser = $this->createMock('Drupal\name\NameFormatParser');
    $container->set('name.format_parser', $this->nameParser);

    $this->nameGenerator = $this->createMock('Drupal\name\NameGeneratorInterface');
    $container->set('name.generator', $this->nameGenerator);

    $this->nameFormatStorage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');
    $this->nameListFormatStorage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');

    \Drupal::setContainer($container);

    $this->fieldDefinition = $this->createMock('Drupal\Core\Field\FieldDefinitionInterface');
    $configuration = [
      'field_definition' => $this->fieldDefinition,
      'settings' => [
        'markup' => 'western',
        'link_target' => 'name',
      ],
      'label' => 'above',
      'view_mode' => 'default',
      'third_party_settings' => [],
    ];
    $plugin_definition = [];
    $this->plugin = NameFormatter::create($container, $configuration, 'name_default', $plugin_definition);
  }

  /**
   * Tests the create method.
   *
   * @covers ::create
   */
  public function testCreate() {
    $container = \Drupal::getContainer();
    $configuration = [
      'field_definition' => $this->fieldDefinition,
      'settings' => [],
      'label' => 'above',
      'view_mode' => 'default',
      'third_party_settings' => [],
    ];
    $plugin_definition = [];
    $plugin = NameFormatter::create($container, $configuration, 'name_default', $plugin_definition);

    $this->assertInstanceOf('Drupal\name\Plugin\Field\FieldFormatter\NameFormatter', $plugin);
  }

  /**
   * Tests the construct method.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $field = new NameFormatter(
      'name_default',
      [],
      $this->fieldDefinition,
      [],
      'above',
      'default',
      [],
      $this->entityFieldManager,
      $this->entityTypeManager,
      $this->renderer,
      $this->nameFormatter,
      $this->nameParser,
      $this->nameGenerator);

    $this->assertInstanceOf('Drupal\name\Plugin\Field\FieldFormatter\NameFormatter', $field);
  }

  /**
   * Tests the defaultSettings method.
   *
   * @covers ::defaultSettings
   */
  public function testDefaultSettings(): void {
    $settings = $this->plugin::defaultSettings();

    $this->assertCount(8, $settings);
    $this->assertArrayHasKey('format', $settings);
    $this->assertArrayHasKey('list_format', $settings);
    $this->assertArrayHasKey('link_target', $settings);
    $this->assertArrayHasKey('markup', $settings);
  }

  /**
   * Tests the settingsForm method.
   *
   * @covers ::settingsForm
   * @covers ::getLinkableTargets
   */
  public function testSettingsForm(): void {

    $this->fieldDefinition->expects($this->exactly(4))
      ->method('getTargetBundle')
      ->willReturn(['node' => 'Content']);

    $this->fieldDefinition->expects($this->exactly(3))
      ->method('getTargetEntityTypeId')
      ->willReturn('node');

    $this->nameFormatStorage->expects($this->once())
      ->method('loadMultiple')
      ->willReturn([]);

    $this->nameListFormatStorage->expects($this->once())
      ->method('loadMultiple')
      ->willReturn([]);

    $entity_type = $this->createMock('Drupal\Core\Entity\EntityTypeInterface');
    $entity_type->expects($this->exactly(2))
      ->method('getBundleLabel')
      ->willReturn('Content');
    $node_storage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');
    $node_storage->expects($this->exactly(2))
      ->method('getEntityType')
      ->willReturn($entity_type);

    $this->entityTypeManager->expects($this->exactly(4))
      ->method('getStorage')
      ->willReturnMap([
        ['name_format', $this->nameFormatStorage],
        ['name_list_format', $this->nameListFormatStorage],
        ['node', $node_storage],
      ]);

    $storage_definition = $this->createMock('Drupal\Core\Field\FieldStorageDefinitionInterface');
    $storage_definition->expects($this->exactly(3))
      ->method('isBaseField')
      ->willReturn(FALSE);
    $reference_field = $this->createMock('Drupal\Core\Field\FieldDefinitionInterface');
    $reference_field->expects($this->exactly(3))
      ->method('getFieldStorageDefinition')
      ->willReturn($storage_definition);
    $reference_field->expects($this->once())
      ->method('getType')
      ->willReturn('entity_reference');
    $this->entityFieldManager->expects($this->exactly(3))
      ->method('getFieldDefinitions')
      ->willReturn([$reference_field]);

    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form = $this->plugin->settingsForm($form, $form_state);

    $this->assertArrayHasKey('format', $form);
  }

  /**
   * Tests the settingsForm method.
   *
   * @covers ::settingsForm
   * @covers ::getLinkableTargets
   */
  public function testSettingsFormLink(): void {

    $this->fieldDefinition->expects($this->exactly(4))
      ->method('getTargetBundle')
      ->willReturn(['node' => 'Content']);

    $this->fieldDefinition->expects($this->exactly(3))
      ->method('getTargetEntityTypeId')
      ->willReturn('node');

    $this->nameFormatStorage->expects($this->once())
      ->method('loadMultiple')
      ->willReturn([]);

    $this->nameListFormatStorage->expects($this->once())
      ->method('loadMultiple')
      ->willReturn([]);

    $entity_type = $this->createMock('Drupal\Core\Entity\EntityTypeInterface');
    $entity_type->expects($this->exactly(2))
      ->method('getBundleLabel')
      ->willReturn('Content');
    $node_storage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');
    $node_storage->expects($this->exactly(2))
      ->method('getEntityType')
      ->willReturn($entity_type);

    $this->entityTypeManager->expects($this->exactly(4))
      ->method('getStorage')
      ->willReturnMap([
        ['name_format', $this->nameFormatStorage],
        ['name_list_format', $this->nameListFormatStorage],
        ['node', $node_storage],
      ]);

    $storage_definition = $this->createMock('Drupal\Core\Field\FieldStorageDefinitionInterface');
    $storage_definition->expects($this->exactly(3))
      ->method('isBaseField')
      ->willReturn(FALSE);
    $link_field = $this->createMock('Drupal\Core\Field\FieldDefinitionInterface');
    $link_field->expects($this->exactly(3))
      ->method('getFieldStorageDefinition')
      ->willReturn($storage_definition);
    $link_field->expects($this->once())
      ->method('getType')
      ->willReturn('link');
    $this->entityFieldManager->expects($this->exactly(3))
      ->method('getFieldDefinitions')
      ->willReturn([$link_field]);

    $form = [];
    $form_state = $this->createMock('Drupal\Core\Form\FormStateInterface');
    $form = $this->plugin->settingsForm($form, $form_state);

    $this->assertArrayHasKey('format', $form);
  }

  /**
   * Tests the settingsSummary method.
   *
   * @covers ::settingsSummary
   */
  public function testSettingsSummary(): void {

    $this->fieldDefinition->expects($this->once())
      ->method('getTargetBundle')
      ->willReturn('article');

    $this->fieldDefinition->expects($this->once())
      ->method('getTargetEntityTypeId')
      ->willReturn('node');

    $this->entityFieldManager->expects($this->once())
      ->method('getFieldDefinitions')
      ->with('node', 'article')
      ->willReturn([]);

    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('name_format')
      ->willReturn($this->nameFormatStorage);

    $name_format = $this->createMock('Drupal\name\NameFormatInterface');
    $this->nameFormatStorage->expects($this->once())
      ->method('load')
      ->with('default')
      ->willReturn($name_format);

    $this->nameGenerator->expects($this->once())
      ->method('loadSampleValues')
      ->willReturn([['given_name' => 'George', 'family_name' => 'Washington']]);

    $this->nameParser->expects($this->once())
      ->method('getMarkupOptions')
      ->willReturn(['western' => 'Western']);

    $summary = $this->plugin->settingsSummary();

    $this->assertIsArray($summary);
    $this->assertCount(7, $summary);
  }

  /**
   * Tests the settingsSummary method.
   *
   * @covers ::settingsSummary
   */
  public function testSettingsSummaryHasLinkTarget(): void {
    $this->entityFieldManager->expects($this->exactly(1))
      ->method('getFieldDefinitions')
      ->willReturn([]);

    $this->plugin->setSetting('link_target', 'node');
    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('name_format')
      ->willReturn($this->nameFormatStorage);

    $name_format = $this->createMock('Drupal\name\NameFormatInterface');
    $this->nameFormatStorage->expects($this->once())
      ->method('load')
      ->with('default')
      ->willReturn($name_format);

    $this->nameGenerator->expects($this->once())
      ->method('loadSampleValues')
      ->willReturn([['given_name' => 'George', 'family_name' => 'Washington']]);

    $this->nameParser->expects($this->once())
      ->method('getMarkupOptions')
      ->willReturn(['western' => 'Western']);

    $summary = $this->plugin->settingsSummary();

    $this->assertIsArray($summary);
    $this->assertCount(7, $summary);
  }

  /**
   * Tests the settingsSummary method.
   *
   * @covers ::settingsSummary
   */
  public function testSettingsSummaryMissingFormat(): void {
    $this->fieldDefinition->expects($this->once())
      ->method('getTargetBundle')
      ->willReturn('article');
    $this->fieldDefinition->expects($this->once())
      ->method('getTargetEntityTypeId')
      ->willReturn('node');
    $this->entityFieldManager->expects($this->once())
      ->method('getFieldDefinitions')
      ->with('node', 'article')
      ->willReturn([]);

    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('name_format')
      ->willReturn($this->nameFormatStorage);

    $this->nameFormatStorage->expects($this->once())
      ->method('load')
      ->with('default')
      ->willReturn(NULL);

    $this->nameParser->expects($this->once())
      ->method('getMarkupOptions')
      ->willReturn(['western' => 'Western']);

    $summary = $this->plugin->settingsSummary();

    $this->assertIsArray($summary);
    $this->assertCount(6, $summary);
  }

  /**
   * Tests the settingsSummary method.
   *
   * @covers ::settingsSummary
   */
  public function testSettingsSummaryHasListFormat(): void {

    $this->fieldDefinition->expects($this->once())
      ->method('getTargetBundle')
      ->willReturn('article');
    $this->fieldDefinition->expects($this->once())
      ->method('getTargetEntityTypeId')
      ->willReturn('node');
    $this->entityFieldManager->expects($this->once())
      ->method('getFieldDefinitions')
      ->with('node', 'article')
      ->willReturn([]);

    $this->plugin->setSetting('list_format', 'default');
    $this->entityTypeManager->expects($this->exactly(2))
      ->method('getStorage')
      ->willReturnMap([
        ['name_format', $this->nameFormatStorage],
        ['name_list_format', $this->nameListFormatStorage],
      ]);

    $name_format = $this->createMock('Drupal\name\NameFormatInterface');
    $this->nameFormatStorage->expects($this->once())
      ->method('load')
      ->with('default')
      ->willReturn($name_format);

    $name_list_format = $this->createMock('Drupal\name\NameListFormatInterface');
    $this->nameListFormatStorage->expects($this->once())
      ->method('load')
      ->with('default')
      ->willReturn($name_list_format);

    $this->nameParser->expects($this->once())
      ->method('getMarkupOptions')
      ->willReturn(['western' => 'Western']);

    $this->nameParser->expects($this->once())
      ->method('parse')
      ->willReturn('George Washington');

    $this->nameGenerator->expects($this->once())
      ->method('loadSampleValues')
      ->willReturn([['given_name' => 'George', 'family_name' => 'Washington']]);

    $summary = $this->plugin->settingsSummary();

    $this->assertIsArray($summary);
    $this->assertCount(7, $summary);
  }

  /**
   * Tests the settingsSummary method.
   *
   * @covers ::settingsSummary
   */
  public function testSettingsSummaryMissingListFormat(): void {
    $this->fieldDefinition->expects($this->once())
      ->method('getTargetBundle')
      ->willReturn('article');
    $this->fieldDefinition->expects($this->once())
      ->method('getTargetEntityTypeId')
      ->willReturn('node');
    $this->entityFieldManager->expects($this->once())
      ->method('getFieldDefinitions')
      ->with('node', 'article')
      ->willReturn([]);

    $this->plugin->setSetting('list_format', 'default');
    $this->entityTypeManager->expects($this->exactly(2))
      ->method('getStorage')
      ->willReturnMap([
        ['name_format', $this->nameFormatStorage],
        ['name_list_format', $this->nameListFormatStorage],
      ]);

    $name_format = $this->createMock('Drupal\name\NameFormatInterface');
    $this->nameFormatStorage->expects($this->once())
      ->method('load')
      ->with('default')
      ->willReturn($name_format);

    $this->nameParser->expects($this->once())
      ->method('getMarkupOptions')
      ->willReturn(['western' => 'Western']);

    $summary = $this->plugin->settingsSummary();

    $this->assertIsArray($summary);
    $this->assertCount(6, $summary);
  }

  /**
   * Tests the useMarkup method.
   *
   * @covers ::useMarkup
   */
  public function testUseMarkup(): void {
    $this->plugin->setSetting('markup', 'western');
    $reflection = new \ReflectionClass($this->plugin);
    $method = $reflection->getMethod('useMarkup');
    $method->setAccessible(TRUE);

    $this->assertEquals('western', $method->invoke($this->plugin));
  }

  /**
   * Tests the viewElements method.
   *
   * @covers ::viewElements
   */
  public function testViewElementsNoItems(): void {

    $items = $this->createMock('Drupal\Core\Field\FieldItemListInterface');
    $items->expects($this->once())
      ->method('count')
      ->willReturn(0);

    $elements = $this->plugin->viewElements($items, 'en');

    $this->assertCount(0, $elements);
  }

  /**
   * Tests the viewElements method.
   *
   * @covers ::viewElements
   * @covers ::getLinkableTargetUrl
   * @covers ::parseAdditionalComponents
   * @covers ::getFieldDefinition
   */
  public function testViewElementsSelf(): void {
    $this->plugin->setSetting('list_format', 'default');
    $this->plugin->setSetting('link_target', '_self');
    $field_items = [
      $this->createMock('Drupal\Core\Field\FieldItemInterface'),
      $this->createMock('Drupal\Core\Field\FieldItemInterface'),
    ];

    $entity = $this->createMock('Drupal\Core\Entity\EntityInterface');
    $entity->expects($this->once())
      ->method('isNew')
      ->willReturn(FALSE);
    $entity->expects($this->once())
      ->method('access')
      ->willReturn(TRUE);
    $entity->expects($this->once())
      ->method('toUrl')
      ->willReturn('http://example.com');

    $items = $this->createMock('Drupal\Core\Field\FieldItemListInterface');
    $items->expects($this->once())
      ->method('getEntity')
      ->willReturn($entity);
    $items->expects($this->once())
      ->method('count')
      ->willReturn(2);

    $field_storage = $this->createMock('Drupal\Core\Field\FieldStorageDefinitionInterface');
    $field_storage->expects($this->once())
      ->method('isMultiple')
      ->willReturn(TRUE);
    $this->fieldDefinition->expects($this->once())
      ->method('getFieldStorageDefinition')
      ->willReturn($field_storage);

    $elements = $this->plugin->viewElements($items, 'en');

    $this->assertCount(1, $elements);
  }

  /**
   * Tests the viewElements method.
   *
   * @covers ::viewElements
   * @covers ::getLinkableTargetUrl
   * @covers ::parseAdditionalComponents
   */
  public function testViewElements(): void {
    $this->plugin->setSetting('list_format', 'default');
    $this->plugin->setSetting('link_target', 'field_name');
    $target_items = $this->createMock('Drupal\Core\Field\FieldItemList');
    $target_items->expects($this->once())
      ->method('isEmpty')
      ->willReturn(TRUE);

    $entity = $this->createMock('Drupal\Core\Entity\ContentEntityBase');

    $entity->expects($this->once())
      ->method('hasField')
      ->with('field_name')
      ->willReturn(TRUE);
    $entity->expects($this->once())
      ->method('get')
      ->with('field_name')
      ->willReturn($target_items);

    $items = $this->createMock('Drupal\Core\Field\FieldItemList');
    $items->expects($this->once())
      ->method('getEntity')
      ->willReturn($entity);
    $items->expects($this->once())
      ->method('count')
      ->willReturn(2);
    $items->expects($this->once())
      ->method('getIterator')
      ->willReturn(new \ArrayIterator([]));

    $field_storage = $this->createMock('Drupal\Core\Field\FieldStorageDefinitionInterface');
    $field_storage->expects($this->once())
      ->method('isMultiple')
      ->willReturn(TRUE);
    $this->fieldDefinition->expects($this->once())
      ->method('getFieldStorageDefinition')
      ->willReturn($field_storage);

    $elements = $this->plugin->viewElements($items, 'en');

    $this->assertCount(1, $elements);
  }

  /**
   * Tests the viewElements method with markup setting assignment.
   *
   * @covers ::viewElements
   */
  public function testViewElementsMarkupSetting(): void {
    $this->plugin->setSetting('list_format', '');
    $this->plugin->setSetting('markup', 'western');

    $field_item = $this->createMock('Drupal\Core\Field\FieldItemInterface');
    $field_item->expects($this->once())
      ->method('toArray')
      ->willReturn(['given_name' => 'John', 'family_name' => 'Doe']);

    $entity = $this->createMock('Drupal\Core\Entity\ContentEntityInterface');
    $entity->expects($this->any())
      ->method('hasField')
      ->willReturn(FALSE);

    $items = $this->createMock('Drupal\Core\Field\FieldItemList');
    $items->expects($this->once())
      ->method('count')
      ->willReturn(1);
    $items->method('getIterator')
      ->willReturn(new \ArrayIterator([$field_item]));
    $items->method('getEntity')
      ->willReturn($entity);

    $field_storage = $this->createMock('Drupal\Core\Field\FieldStorageDefinitionInterface');
    $field_storage->expects($this->once())
      ->method('isMultiple')
      ->willReturn(FALSE);
    $this->fieldDefinition->expects($this->once())
      ->method('getFieldStorageDefinition')
      ->willReturn($field_storage);

    // Mock the formatter to verify markup setting is called.
    $this->nameFormatter->expects($this->once())
      ->method('setSetting')
      ->with('markup', 'western');

    $this->nameFormatter->expects($this->once())
      ->method('format')
      ->willReturnCallback(function ($components, $format, $langcode) {
        if (isset($components['given_name']) && $components['given_name'] === 'John') {
          return 'John Doe';
        }
        return 'Unknown Name';
      });

    $elements = $this->plugin->viewElements($items, 'en');

    $this->assertCount(1, $elements);
    $this->assertArrayHasKey(0, $elements);
    $this->assertEquals('John Doe', $elements[0]['#markup']);
  }

  /**
   * Tests the viewElements method with individual item processing loop.
   *
   * @covers ::viewElements
   */
  public function testViewElementsIndividualItemsLoop(): void {
    $this->plugin->setSetting('list_format', '');
    $this->plugin->setSetting('markup', 'none');

    $field_item1 = $this->createMock('Drupal\Core\Field\FieldItemInterface');
    $field_item1->expects($this->once())
      ->method('toArray')
      ->willReturn(['given_name' => 'John', 'family_name' => 'Doe']);

    $field_item2 = $this->createMock('Drupal\Core\Field\FieldItemInterface');
    $field_item2->expects($this->once())
      ->method('toArray')
      ->willReturn(['given_name' => 'Jane', 'family_name' => 'Smith']);

    $entity = $this->createMock('Drupal\Core\Entity\ContentEntityInterface');
    $entity->expects($this->any())
      ->method('hasField')
      ->willReturn(FALSE);

    $items = $this->createMock('Drupal\Core\Field\FieldItemList');
    $items->expects($this->once())
      ->method('count')
      ->willReturn(2);
    $items->method('getIterator')
      ->willReturn(new \ArrayIterator([$field_item1, $field_item2]));
    $items->method('getEntity')
      ->willReturn($entity);

    $field_storage = $this->createMock('Drupal\Core\Field\FieldStorageDefinitionInterface');
    $field_storage->expects($this->once())
      ->method('isMultiple')
      ->willReturn(TRUE);
    $this->fieldDefinition->expects($this->once())
      ->method('getFieldStorageDefinition')
      ->willReturn($field_storage);

    // Mock the formatter to verify markup setting is called once.
    $this->nameFormatter->expects($this->once())
      ->method('setSetting')
      ->with('markup', 'none');

    $this->nameFormatter->expects($this->exactly(2))
      ->method('format')
      ->willReturnCallback(function ($components, $format, $langcode) {
        if (isset($components['given_name']) && $components['given_name'] === 'John') {
          return 'John Doe';
        }
        if (isset($components['given_name']) && $components['given_name'] === 'Jane') {
          return 'Jane Smith';
        }
        return 'Unknown Name';
      });

    $elements = $this->plugin->viewElements($items, 'en');

    $this->assertCount(2, $elements);
    $this->assertArrayHasKey(0, $elements);
    $this->assertArrayHasKey(1, $elements);
    $this->assertEquals('John Doe', $elements[0]['#markup']);
    $this->assertEquals('Jane Smith', $elements[1]['#markup']);
  }

  /**
   * Tests getLinkableTargetUrl().
   *
   * @covers ::getLinkableTargetUrl
   * @covers ::getFieldDefinition
   */
  public function testGetLinkableTargetUrlEntityReference(): void {
    // $this->plugin->setSetting('link_target', 'field_name');
    $field_definition = $this->createMock('Drupal\Core\Field\FieldDefinitionInterface');
    $field_definition->expects($this->once())
      ->method('getType')
      ->willReturn('entity_reference');

    $entity = $this->createMock('Drupal\Core\Entity\ContentEntityInterface');
    $entity->expects($this->once())
      ->method('isNew')
      ->willReturn(FALSE);
    $entity->expects($this->once())
      ->method('access')
      ->with('view')
      ->willReturn(TRUE);
    $entity->expects($this->once())
      ->method('toUrl')
      ->willReturn('http://example.com');

    $item = $this->createMock(TestEntityReferenceItem::class);
    $item->entity = $entity;

    $target_items = $this->createMock('Drupal\Core\Field\FieldItemList');
    $target_items->expects($this->once())
      ->method('isEmpty')
      ->willReturn(FALSE);
    $target_items->expects($this->once())
      ->method('getFieldDefinition')
      ->willReturn($field_definition);
    $target_items->expects($this->once())
      ->method('getIterator')
      ->willReturn(new \ArrayIterator([$item]));

    $parent = $this->createMock('Drupal\Core\Entity\ContentEntityInterface');
    $parent->expects($this->once())
      ->method('hasField')
      ->with('name')
      ->willReturn(TRUE);
    $parent->expects($this->once())
      ->method('get')
      ->with('name')
      ->willReturn($target_items);

    $items = $this->createMock('Drupal\Core\Field\FieldItemListInterface');
    $items->expects($this->once())
      ->method('getEntity')
      ->willReturn($parent);

    // Make accessible.
    $reflection = new \ReflectionClass($this->plugin);
    $method = $reflection->getMethod('getLinkableTargetUrl');
    $method->setAccessible(TRUE);
    $url = $method->invoke($this->plugin, $items);

  }

  /**
   * Tests getLinkableTargetUrl().
   *
   * @covers ::getLinkableTargetUrl
   * @covers ::getFieldDefinition
   */
  public function testGetLinkableTargetUrlNoEntity(): void {

    $field_definition = $this->createMock('Drupal\Core\Field\FieldDefinitionInterface');
    $field_definition->expects($this->once())
      ->method('getType')
      ->willReturn('entity_reference');

    $target_items = $this->createMock('Drupal\Core\Field\FieldItemList');
    $target_items->expects($this->once())
      ->method('isEmpty')
      ->willReturn(FALSE);
    $target_items->expects($this->once())
      ->method('getFieldDefinition')
      ->willReturn($field_definition);
    $target_items->expects($this->once())
      ->method('getIterator')
      ->willReturn(new \ArrayIterator([]));

    $parent = $this->createMock('Drupal\Core\Entity\ContentEntityInterface');
    $parent->expects($this->once())
      ->method('hasField')
      ->with('name')
      ->willReturn(TRUE);
    $parent->expects($this->once())
      ->method('get')
      ->with('name')
      ->willReturn($target_items);

    $items = $this->createMock('Drupal\Core\Field\FieldItemListInterface');
    $items->expects($this->once())
      ->method('getEntity')
      ->willReturn($parent);

    // Make accessible.
    $reflection = new \ReflectionClass($this->plugin);
    $method = $reflection->getMethod('getLinkableTargetUrl');
    $method->setAccessible(TRUE);
    $url = $method->invoke($this->plugin, $items);

  }

  /**
   * Tests getLinkableTargetUrl().
   *
   * @covers ::getLinkableTargetUrl
   * @covers ::getFieldDefinition
   */
  public function testGetLinkableTargetUrlLink(): void {

    $field_definition = $this->createMock('Drupal\Core\Field\FieldDefinitionInterface');
    $field_definition->expects($this->once())
      ->method('getType')
      ->willReturn('link');

    $item = $this->createMock('Drupal\link\Plugin\Field\FieldType\LinkItem');
    $item->expects($this->once())
      ->method('getUrl')
      ->willReturn('http://example.com');

    $target_items = $this->createMock('Drupal\Core\Field\FieldItemList');
    $target_items->expects($this->once())
      ->method('isEmpty')
      ->willReturn(FALSE);
    $target_items->expects($this->once())
      ->method('getFieldDefinition')
      ->willReturn($field_definition);
    $target_items->expects($this->once())
      ->method('getIterator')
      ->willReturn(new \ArrayIterator([$item]));

    $parent = $this->createMock('Drupal\Core\Entity\ContentEntityInterface');
    $parent->expects($this->once())
      ->method('hasField')
      ->with('name')
      ->willReturn(TRUE);
    $parent->expects($this->once())
      ->method('get')
      ->with('name')
      ->willReturn($target_items);

    $items = $this->createMock('Drupal\Core\Field\FieldItemListInterface');
    $items->expects($this->once())
      ->method('getEntity')
      ->willReturn($parent);

    // Make accessible.
    $reflection = new \ReflectionClass($this->plugin);
    $method = $reflection->getMethod('getLinkableTargetUrl');
    $method->setAccessible(TRUE);
    $url = $method->invoke($this->plugin, $items);

  }

  /**
   * Tests getLinkableTargetUrl().
   *
   * @covers ::getLinkableTargetUrl
   * @covers ::getFieldDefinition
   */
  public function testGetLinkableTargetUrlException(): void {

    $field_definition = $this->createMock('Drupal\Core\Field\FieldDefinitionInterface');
    $field_definition->expects($this->once())
      ->method('getType')
      ->willReturn('link');

    $item = $this->createMock('Drupal\link\Plugin\Field\FieldType\LinkItem');
    // Throw exception.
    $item->expects($this->once())
      ->method('getUrl')
      ->will($this->throwException(new UndefinedLinkTemplateException('error')));
    // ->willThrow(new UndefinedLinkTemplateException('error'));
    $target_items = $this->createMock('Drupal\Core\Field\FieldItemList');
    $target_items->expects($this->once())
      ->method('isEmpty')
      ->willReturn(FALSE);
    $target_items->expects($this->once())
      ->method('getFieldDefinition')
      ->willReturn($field_definition);
    $target_items->expects($this->once())
      ->method('getIterator')
      ->willReturn(new \ArrayIterator([$item]));

    $parent = $this->createMock('Drupal\Core\Entity\ContentEntityInterface');
    $parent->expects($this->once())
      ->method('hasField')
      ->with('name')
      ->willReturn(TRUE);
    $parent->expects($this->once())
      ->method('get')
      ->with('name')
      ->willReturn($target_items);

    $items = $this->createMock('Drupal\Core\Field\FieldItemListInterface');
    $items->expects($this->once())
      ->method('getEntity')
      ->willReturn($parent);

    // Make accessible.
    $reflection = new \ReflectionClass($this->plugin);
    $method = $reflection->getMethod('getLinkableTargetUrl');
    $method->setAccessible(TRUE);
    $url = $method->invoke($this->plugin, $items);

  }

  /**
   * Tests parseAdditionalComponents().
   *
   * @covers ::parseAdditionalComponents
   */
  public function testParseAdditionalComponents(): void {

    $this->plugin->setSetting('preferred_field_reference', '_self');
    $this->plugin->setSetting('preferred_field_reference_separator', '|');

    $entity = $this->createMock('Drupal\Core\Entity\ContentEntityBase');
    $entity->expects($this->once())
      ->method('label')
      ->willReturn('George Washington');

    $item = $this->createMock('Drupal\link\Plugin\Field\FieldType\LinkItem');

    $items = $this->createMock('Drupal\Core\Field\FieldItemList');
    $items->expects($this->once())
      ->method('getEntity')
      ->willReturn($entity);

    // Make accessible.
    $reflection = new \ReflectionClass($this->plugin);
    $method = $reflection->getMethod('parseAdditionalComponents');
    $method->setAccessible(TRUE);
    $extra = $method->invoke($this->plugin, $items);

    $this->assertIsArray($extra);
    $this->assertCount(1, $extra);
    $this->assertArrayHasKey('preferred', $extra);
  }

}

/**
 * Test class for EntityReferenceItem.
 *
 * The entity property exists to allow testing.
 */
class TestEntityReferenceItem extends EntityReferenceItem {
  /**
   * The entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  public $entity;

}
