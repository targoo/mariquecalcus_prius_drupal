<?php

/**
 * @file
 * Contains \Drupal\language\Tests\EntityDefaultLanguageTest.
 */

namespace Drupal\language\Tests;

use Drupal\Core\Language\LanguageInterface;
use Drupal\simpletest\KernelTestBase;

/**
 * Tests default language code is properly generated for entities.
 */
class EntityDefaultLanguageTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('language', 'node', 'field', 'text', 'user');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Entity default language',
      'description' => 'Test that entities are created with correct language code.',
      'group' => 'Entity API',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Activate Spanish language, so there are two languages activated.
    $language = $this->container->get('entity.manager')->getStorage('configurable_language')->create(array(
      'id' => 'es',
    ));
    $language->save();

    // Create a new content type which has Undefined language by default.
    $this->createContentType('ctund', LanguageInterface::LANGCODE_NOT_SPECIFIED);
    // Create a new content type which has Spanish language by default.
    $this->createContentType('ctes', 'es');
  }

  /**
   * Tests that default language code is properly set for new nodes.
   */
  public function testEntityTranslationDefaultLanguageViaCode() {
    // With language module activated, and a content type that is configured to
    // have no language by default, a new node of this content type will have
    // "und" language code when language is not specified.
    $node = $this->createNode('ctund');
    $this->assertEqual($node->langcode->value, LanguageInterface::LANGCODE_NOT_SPECIFIED);
    // With language module activated, and a content type that is configured to
    // have no language by default, a new node of this content type will have
    // "es" language code when language is specified as "es".
    $node = $this->createNode('ctund', 'es');
    $this->assertEqual($node->langcode->value, 'es');

    // With language module activated, and a content type that is configured to
    // have language "es" by default, a new node of this content type will have
    // "es" language code when language is not specified.
    $node = $this->createNode('ctes');
    $this->assertEqual($node->langcode->value, 'es');
    // With language module activated, and a content type that is configured to
    // have language "es" by default, a new node of this content type will have
    // "en" language code when language "en" is specified.
    $node = $this->createNode('ctes', 'en');
    $this->assertEqual($node->langcode->value, 'en');

    // Disable language module.
    $this->disableModules(array('language'));

    // With language module disabled, and a content type that is configured to
    // have no language specified by default, a new node of this content type
    // will have site's default language code when language is not specified.
    $node = $this->createNode('ctund');
    $this->assertEqual($node->langcode->value, 'en');
    // With language module disabled, and a content type that is configured to
    // have no language specified by default, a new node of this type will have
    // "es" language code when language "es" is specified.
    $node = $this->createNode('ctund', 'es');
    $this->assertEqual($node->langcode->value, 'es');

    // With language module disabled, and a content type that is configured to
    // have language "es" by default, a new node of this type will have site's
    // default language code when language is not specified.
    $node = $this->createNode('ctes');
    $this->assertEqual($node->langcode->value, 'en');
    // With language module disabled, and a content type that is configured to
    // have language "es" by default, a new node of this type will have "en"
    // language code when language "en" is specified.
    $node = $this->createNode('ctes', 'en');
    $this->assertEqual($node->langcode->value, 'en');
  }

  /**
   * Creates a new node content type.
   *
   * @param name
   *   The content type name.
   * @param $langcode
   *   Default language code of the nodes of this type.
   */
  protected function createContentType($name, $langcode) {
    $content_type = $this->container->get('entity.manager')->getStorage('node_type')->create(array(
      'name' => 'Test ' . $name,
      'title_label' => 'Title',
      'type' => $name,
      'create_body' => FALSE,
    ));
    $content_type->save();
    language_save_default_configuration('node', $name, array(
      'langcode' => $langcode,
      'language_show' => FALSE,
    ));
  }

  /**
   * Creates a new node of given type and language using Entity API.
   *
   * @param $type
   *   The node content type.
   * @param $langcode
   *   (optional) Language code to pass to entity create.
   *
   * @return \Drupal\node\NodeInterface
   *   The node created.
   */
  protected function createNode($type, $langcode = NULL) {
    $values = array(
      'type' => $type,
      'title' => $this->randomString(),
    );
    if (!empty($langcode)) {
      $values['langcode'] = $langcode;
    }
    $node = $this->container->get('entity.manager')->getStorage('node')->create($values);
    return $node;
  }

}
