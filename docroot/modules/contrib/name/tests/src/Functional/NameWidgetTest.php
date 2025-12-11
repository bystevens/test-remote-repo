<?php

namespace Drupal\Tests\name\Functional;

use Behat\Mink\Element\TraversableElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Component\Utility\DeprecationHelper;

/**
 * Various tests on creating a name widget on a node.
 *
 * @group name
 */
class NameWidgetTest extends NameTestBase {

  use NameTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'field',
    'field_ui',
    'node',
    'name',
    'taxonomy',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Create content-type: page.
    $page = NodeType::create([
      'type' => 'page',
      'name' => 'Basic page',
    ]);
    $page->save();
  }

  /**
   * The most basic test.
   */
  public function testFieldEntry() {
    $this->drupalLogin($this->adminUser);

    $this->drupalGet('admin/structure/types/manage/page/fields/add-field');

    DeprecationHelper::backwardsCompatibleCall(
      \Drupal::VERSION,
      '11.2.2',
      fn() => $this->getSession()->getPage()->clickLink('Name'),
      fn() => !$this->getSession()->getPage()->fillField('new_storage_type', 'name') && $this->getSession()->getPage()->pressButton('Continue')
    );

    $new_name_field = [
      'label' => 'Test name',
      'field_name' => 'name_test',
    ];
    $this->submitForm($new_name_field, 'Continue');

    DeprecationHelper::backwardsCompatibleCall(
      \Drupal::VERSION,
      '11.2.2',
      fn() => $this->submitForm([], 'Save'),
      fn() => $this->submitForm([], 'Save settings')
    );

    $this->resetAll();

    // Set up a field of each label display and test it shows.
    $field_settings = [
      'settings[components][title]' => TRUE,
      'settings[components][given]' => TRUE,
      'settings[components][middle]' => TRUE,
      'settings[components][family]' => TRUE,
      'settings[components][generational]' => TRUE,
      'settings[components][credentials]' => TRUE,

      'settings[minimum_components][title]' => TRUE,
      'settings[minimum_components][given]' => TRUE,
      'settings[minimum_components][middle]' => TRUE,
      'settings[minimum_components][family]' => TRUE,
      'settings[minimum_components][generational]' => TRUE,
      'settings[minimum_components][credentials]' => TRUE,

      'settings[show_component_required_marker]' => TRUE,

      'settings[labels][title]' => 'Title',
      'settings[labels][given]' => 'Given',
      'settings[labels][middle]' => 'Middle name(s)',
      'settings[labels][family]' => 'Family',
      'settings[labels][generational]' => 'Generational',
      'settings[labels][credentials]' => 'Credentials',

      'settings[title_display][title]' => 'title',
      'settings[title_display][given]' => 'title',
      'settings[title_display][middle]' => 'description',
      'settings[title_display][family]' => 'placeholder',
      'settings[title_display][generational]' => 'none',
      'settings[title_display][credentials]' => 'placeholder',

      'settings[field_type][title]' => 'select',
      'settings[field_type][given]' => 'text',
      'settings[field_type][middle]' => 'text',
      'settings[field_type][family]' => 'text',
      'settings[field_type][generational]' => 'autocomplete',
      'settings[field_type][credentials]' => 'text',

      'settings[max_length][title]' => 31,
      'settings[max_length][given]' => 45,
      'settings[max_length][middle]' => 127,
      'settings[max_length][family]' => 63,
      'settings[max_length][generational]' => 15,
      'settings[max_length][credentials]' => 255,

      'settings[size][title]' => 6,
      'settings[size][given]' => 10,
      'settings[size][middle]' => 20,
      'settings[size][family]' => 25,
      'settings[size][generational]' => 5,
      'settings[size][credentials]' => 35,

      'settings[credentials_inline]' => TRUE,

      'settings[sort_options][title]' => TRUE,
      'settings[sort_options][generational]' => FALSE,

      'settings[title_options]' => "-- --\nMr.\nMrs.\nMiss\nMs.\nDr.\nProf.",
      'settings[generational_options]' => "-- --\nJr.\nSr.\nI\nII\nIII\nIV\nV\nVI\nVII\nVIII\nIX\nX",

      'settings[component_layout]' => 'default',
    ];
    $this->drupalGet('admin/structure/types/manage/page/fields/node.page.field_name_test');

    DeprecationHelper::backwardsCompatibleCall(
      \Drupal::VERSION,
      '11.2.2',
      fn() => $this->submitForm($field_settings, 'Save'),
      fn() => $this->submitForm($field_settings, 'Save settings')
    );

    $this->drupalGet('node/add/page');

    $this->assertSession()->selectExists('field_name_test[0][title]');
    $this->inputFieldExists('field_name_test[0][given]');
    $this->inputFieldExists('field_name_test[0][middle]');
    $this->inputFieldExists('field_name_test[0][family]');
    $this->inputFieldExists('field_name_test[0][generational]');
    $this->inputFieldExists('field_name_test[0][credentials]');

    // Checks the existence and positioning of the components.
    foreach (_name_component_keys() as $component) {
      $this->assertComponentSettings($component, $field_settings);
    }

    $this->assertFieldSettings($field_settings);

    // Test the language layouts.
    $field_settings['settings[component_layout]'] = 'asian';
    $this->drupalGet('admin/structure/types/manage/page/fields/node.page.field_name_test');
    $this->submitForm($field_settings, 'Save settings');
    $this->drupalGet('node/add/page');
    $this->assertFieldSettings($field_settings);

    $field_settings['settings[component_layout]'] = 'eastern';
    $this->drupalGet('admin/structure/types/manage/page/fields/node.page.field_name_test');
    $this->submitForm($field_settings, 'Save settings');
    $this->drupalGet('node/add/page');
    $this->assertFieldSettings($field_settings);

    $field_settings['settings[component_layout]'] = 'german';
    $this->drupalGet('admin/structure/types/manage/page/fields/node.page.field_name_test');
    $this->submitForm($field_settings, 'Save settings');
    $this->drupalGet('node/add/page');
    $this->assertFieldSettings($field_settings);

    $field_settings = [
      'settings[show_component_required_marker]' => FALSE,
      'settings[component_layout]' => 'default',
      // 'settings[credentials_inline]' => TRUE,
      // 'settings[component_layout]' => 'default',
    ] + $field_settings;
    $this->drupalGet('admin/structure/types/manage/page/fields/node.page.field_name_test');
    $this->submitForm($field_settings, 'Save settings');
    $this->drupalGet('node/add/page');
    foreach (_name_component_keys() as $component) {
      $this->assertComponentSettings($component, $field_settings);
    }

  }

  /**
   * Asserts that the field settings appear in the correct order.
   *
   * @param array $settings
   *   The field settings, as form post array.
   */
  protected function assertFieldSettings(array $settings) {
    $xpath = '//div[@id="edit-field-name-test-wrapper"]/div/div';
    $elements = $this->xpath($xpath);
    $this->assertNotEmpty($elements, 'No components found.');

    $content = '';
    foreach ($elements as $element) {
      $content .= str_replace(["\n", "\r"], " ", $element->getHtml());
    }

    switch ($settings["settings[component_layout]"]) {
      case 'asian':
        $regexp = '/name-family-wrapper.*name-middle-wrapper.*name-given-wrapper.*name-title-wrapper.*name-credentials-wrapper.*/';
        if (strpos($content, 'name-generational-wrapper')) {
          $this->assertTrue(FALSE, "Generational field is not rendered with asian layout.");
        }
        break;

      case 'eastern':
        $regexp = '/name-title-wrapper.*name-family-wrapper.*name-given-wrapper.*name-middle-wrapper.*name-generational-wrapper.*name-credentials-wrapper.*/';
        break;

      case 'german':
        $regexp = '/name-title-wrapper.*name-credentials-wrapper.*name-given-wrapper.*name-middle-wrapper.*name-family-wrapper.*/';
        if (strpos($content, 'name-generational-wrapper')) {
          $this->assertTrue(FALSE, "Generational field is not rendered with german layout.");
        }
        break;

      case 'default':
      default:
        $regexp = '/name-title-wrapper.*name-given-wrapper.*name-middle-wrapper.*name-family-wrapper.*name-generational-wrapper.*name-credentials-wrapper.*/';
        break;
    }

    $this->assertTrue((bool) preg_match($regexp, $content), 'Generational field wrapper classes appear to be in the correct order.');

    // @todo Tests for settings[credentials_inline] setting.
  }

  /**
   * Asserts that the components exists and appear in the right order.
   *
   * @param string $key
   *   The name component key, for example 'given'.
   * @param array $settings
   *   The field settings, as form post array.
   */
  protected function assertComponentSettings($key, array $settings) {
    $xpath = '//div[contains(@class,:value)]';
    $elements = $this->xpath($this->assertSession()->buildXPathQuery($xpath, [':value' => "name-{$key}-wrapper"]));
    $this->assertNotEmpty($elements, "Component $key field found.");
    $object = reset($elements);

    $type = $settings["settings[field_type][{$key}]"] == 'select' ? 'select' : 'input';
    $show_required = $settings['settings[show_component_required_marker]'];
    $is_required = $settings["settings[minimum_components][{$key}]"];
    $content = str_replace(["\n", "\r"], " ", $object->getHtml());

    switch ($settings["settings[title_display][$key]"]) {
      case 'title':
        $result = (bool) preg_match('/<label .*<' . $type . ' /i', $content);
        $this->assertTrue($result, "Testing label is before field of type $type for $key component.");
        if ($result) {
          $required_marker_preg = '@<label .*?class=".*?js-form-required.*form-required.*?".*>@';
          if ($show_required && $is_required) {
            $this->assertTrue((bool) preg_match($required_marker_preg, $content), "Required class is added for $key component in label");
          }
          else {
            $this->assertFalse((bool) preg_match($required_marker_preg, $content), "Required class is not added for $key component in label");
          }
        }
        break;

      case 'description':
        $result = (bool) preg_match('/<' . $type . ' .*<label /i', $content);
        $this->assertTrue($result, "Testing label is after field of type $type for $key component.");
        if ($result) {
          $required_marker_preg = '@<label .*?class=".*?js-form-required.*form-required.*?">@';
          if ($show_required && $is_required) {
            $this->assertTrue((bool) preg_match($required_marker_preg, $content), "Required class is added for $key component in label");
          }
          else {
            $this->assertFalse((bool) preg_match($required_marker_preg, $content), "Required class is not added for $key component in label");
          }
        }
        break;

      case 'placeholder':
        $result = (bool) preg_match('@<' . $type . ' [^>]*?placeholder=".*?' . $settings["settings[labels][$key]"] . '.*?"@', $content);
        $this->assertTrue($result, "Testing label is a placeholder on the field of type $type for $key component.");
        if ($result) {
          $required_marker_preg = '@<' . $type . ' [^>]*?placeholder=".*?Required.*?"@';
          if ($show_required && $is_required) {
            $this->assertTrue((bool) preg_match($required_marker_preg, $content), "Required text is added for $key component in placeholder attribute");
          }
          else {
            $this->assertFalse((bool) preg_match($required_marker_preg, $content), "Required text is added for $key component in placeholder attribute");
          }
        }
        break;

      case 'attribute':
        $result = (bool) preg_match('@<' . $type . ' [^>]*?title=".*?' . $settings["settings[labels][$key]"] . '.*?"@', $content);
        $this->assertTrue($result, "Testing label is a title attribute on the field of type $type for $key component.");
        if ($result) {
          $required_marker_preg = '@<' . $type . ' [^>]*?title=".*?Required.*?"@';
          if ($show_required && $is_required) {
            $this->assertTrue((bool) preg_match($required_marker_preg, $content), "Required text is added for $key component in $type title attribute");
          }
          else {
            $this->assertFalse((bool) preg_match($required_marker_preg, $content), "Required text is added for $key component in $type title attribute");
          }
        }
        break;

      case 'none':
        $result = (bool) preg_match('@<label [^>]*?class=".*?visually-hidden.*?"@', $content);
        $this->assertTrue($result, "Testing label is present but hidden on the field of type $type for $key component.");
        break;

    }

    if (isset($settings["settings[max_length][{$key}]"]) && $type != 'select') {
      $result = (bool) preg_match('@<' . $type . ' [^>]*?maxlength="' . $settings["settings[max_length][{$key}]"] . '"@', $content);
      $this->assertTrue($result, "Testing max_length is set on field of type $type for $key component.");
    }
    if (isset($settings["settings[size][{$key}]"]) && $type != 'select') {
      $result = (bool) preg_match('@<' . $type . ' [^>]*?size="' . $settings["settings[size][{$key}]"] . '"@', $content);
      $this->assertTrue($result, "Testing size is set on field of type $type for $key component.");
    }
  }

  /**
   * Checks that specific input field exists on the current page.
   *
   * @param string $name
   *   One of id|name|label|value for the input field.
   * @param \Behat\Mink\Element\TraversableElement $container
   *   (optional) The document to check against. Defaults to the current page.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The matching element.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   *   When the element doesn't exist.
   */
  public function inputFieldExists($name, ?TraversableElement $container = NULL) {
    $container = $container ?: $this->getSession()->getPage();
    $node = $container->find('named', [
      'field',
      $name,
    ]);

    if ($node === NULL || $node->getTagName() != 'input') {
      throw new ElementNotFoundException($this->getSession()->getDriver(), 'input', 'id|name|label|value', $name);
    }

    return $node;
  }

  /**
   * Tests that "_none" is a valid value for text fields.
   */
  public function testTextFieldNoneValue() {
    $this->drupalLogin($this->adminUser);

    // Create a name field, make all components text fields.
    $this->createNameField('field_name_test', 'node', 'page', [], [
      'settings' => [
        'field_type' => [
          'title' => 'text',
          'generational' => 'text',
        ],
      ],
    ]);
    // Add the field to the form display.
    $this->container->get('entity_display.repository')
      ->getFormDisplay('node', 'page', 'default')
      ->setComponent('field_name_test', [
        'type' => 'name_default',
        'weight' => 1,
      ])
      ->save();

    // Add a node and use the value "_none" for each text field.
    $this->drupalGet('node/add/page');
    $edit = [
      'title[0][value]' => 'Test node',
      'field_name_test[0][title]' => '_none',
      'field_name_test[0][given]' => '_none',
      'field_name_test[0][middle]' => '_none',
      'field_name_test[0][family]' => '_none',
      'field_name_test[0][generational]' => '_none',
      'field_name_test[0][credentials]' => '_none',
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContains('Basic page Test node has been created.');

    // Load the node and assert that the value _none was saved for each text
    // field.
    $node = Node::load(1);
    $this->assertEquals('_none', $node->field_name_test->title);
    $this->assertEquals('_none', $node->field_name_test->given);
    $this->assertEquals('_none', $node->field_name_test->middle);
    $this->assertEquals('_none', $node->field_name_test->family);
    $this->assertEquals('_none', $node->field_name_test->generational);
    $this->assertEquals('_none', $node->field_name_test->credentials);

    // Go the node edit form and assert that "_none" is used in each text field.
    $this->drupalGet('node/1/edit');
    $assert_session = $this->assertSession();
    $assert_session->fieldValueEquals('field_name_test[0][title]', '_none');
    $assert_session->fieldValueEquals('field_name_test[0][given]', '_none');
    $assert_session->fieldValueEquals('field_name_test[0][middle]', '_none');
    $assert_session->fieldValueEquals('field_name_test[0][family]', '_none');
    $assert_session->fieldValueEquals('field_name_test[0][generational]', '_none');
    $assert_session->fieldValueEquals('field_name_test[0][credentials]', '_none');
  }

  /**
   * Tests that "_none" can be used for text fields when name field is required.
   */
  public function testTextFieldNoneValueForRequiredNameField() {
    $this->drupalLogin($this->adminUser);

    // Create a name field and set it as required.
    $this->createNameField('field_name_test', 'node', 'page', [], [
      'required' => TRUE,
    ]);
    // Add the field to the form display.
    $this->container->get('entity_display.repository')
      ->getFormDisplay('node', 'page', 'default')
      ->setComponent('field_name_test', [
        'type' => 'name_default',
        'weight' => 1,
      ])
      ->save();

    // Add a node, first check if name field is indeed required.
    $this->drupalGet('node/add/page');
    $edit = [
      'title[0][value]' => 'Test node',
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContains('field_name_test field is required.');

    // Now set the values.
    $edit = [
      'field_name_test[0][given]' => '_none',
      'field_name_test[0][family]' => '_none',
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContains('Basic page Test node has been created.');

    // Load the node and assert that the value "_none" was saved for fields
    // 'given' and 'family'.
    $node = Node::load(1);
    $this->assertEquals('_none', $node->field_name_test->given);
    $this->assertEquals('_none', $node->field_name_test->family);

    // Go the node edit form and assert that "_none" is used for 'given' and
    // 'family'.
    $this->drupalGet('node/1/edit');
    $assert_session = $this->assertSession();
    $assert_session->fieldValueEquals('field_name_test[0][given]', '_none');
    $assert_session->fieldValueEquals('field_name_test[0][family]', '_none');
  }

  /**
   * Tests that multiple unselected values are properly handled.
   */
  public function testMultipleNoneValues() {
    $this->drupalLogin($this->adminUser);

    // Create a name field.
    $this->drupalGet('admin/structure/types/manage/page/fields/add-field');

    DeprecationHelper::backwardsCompatibleCall(
      \Drupal::VERSION,
      '11.2.2',
      fn() => $this->getSession()->getPage()->clickLink('Name'),
      fn() => !$this->getSession()->getPage()->fillField('new_storage_type', 'name') && $this->getSession()->getPage()->pressButton('Continue')
    );

    $new_name_field = [
      'label' => 'Test name',
      'field_name' => 'name_test',
    ];
    $this->submitForm($new_name_field, 'Continue');

    // Configure field settings with title and generational as select fields.
    $field_settings = [
      'settings[components][title]' => TRUE,
      'settings[components][generational]' => TRUE,
      'settings[field_type][title]' => 'select',
      'settings[field_type][generational]' => 'select',
      'settings[title_options]' => "-- --\nMr.\nMrs.\nDr.",
      'settings[generational_options]' => "-- --\nJr.\nSr.\nIII",
    ];

    DeprecationHelper::backwardsCompatibleCall(
      \Drupal::VERSION,
      '11.2.2',
      fn() => $this->submitForm($field_settings, 'Save'),
      fn() => $this->submitForm($field_settings, 'Save settings')
    );

    // Create a node with both title and generational unselected.
    $this->drupalGet('node/add/page');
    $edit = [
      'title[0][value]' => 'Test node',
      'field_name_test[0][title]' => '_none',
      'field_name_test[0][generational]' => '_none',
    ];
    $this->submitForm($edit, 'Save');

    // Load the node and verify both fields are empty strings.
    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(['title' => 'Test node']);
    $node = reset($nodes);

    $this->assertEquals('', $node->field_name_test->title, 'Title field is empty');
    $this->assertEquals('', $node->field_name_test->generational, 'Generational field is empty');
  }

}
