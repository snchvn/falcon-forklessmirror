<?php

namespace falcon_feature_metatag;

/**
 * Class MetatagCest.
 *
 * @package Falcon Configurations
 */
class MetatagCest {

  private $field_storage_config;

  private $field_config;

  private $article;

  /**
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function _before() {
    $entity_type_manager = \Drupal::entityTypeManager();
    $storage = $entity_type_manager->getStorage('field_storage_config');

    // Create field storage config.
    $this->field_storage_config = $storage->create([
      'type' => 'metatag',
      'field_name' => 'test_metatag_field',
      'entity_type' => 'node',
    ]);
    $this->field_storage_config->save();

    $storage = $entity_type_manager->getStorage('field_config');

    // Create field config for node type "news".
    $this->field_config = $storage
      ->create([
        'field_storage' => $this->field_storage_config,
        'bundle' => 'news',
      ]);
    $this->field_config->save();

    // Clear cache.
    foreach (\Drupal\Core\Cache\Cache::getBins() as $service_id => $cache_backend) {
      $cache_backend->deleteAll();
    }

    // Create article.
    $this->article = $entity_type_manager->getStorage('node')->create([
      'uid' => 1,
      'type' => 'news',
      'title' => 'Test news',
      'status' => 1,
      'test_metatag_field' => serialize([
        'description' => 'TEST DESCRIPTION',
        'keywords' => 'TEST KEYWORDS'
      ])
    ]);
    $this->article->save();
  }

  /**
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function _after() {
    \Drupal::entityTypeManager()->getStorage('node')->delete([$this->article]);
    $this->field_config->delete();
    $this->field_storage_config->delete();
  }

  /**
   * Normalized metatags into jsonApi response.
   *
   * @param \ApiTester $I
   * @group basic
   */
  public function metatagsInResponse(\ApiTester $I) {
    $I->amGoingTo('Get request to JsonAPI endpoint. Checks metatags in response.');
    $I->haveHttpHeader('Content-Type', 'application/json');

    $consumer = \Drupal::entityTypeManager()->getStorage('consumer')->load(1);

    // Set consumer header.
    if (!empty($consumer)) {
      $I->haveHttpHeader('X-Consumer-ID', $consumer->uuid());
    }

    $I->sendGET('/jsonapi/node/news');

    $I->expectTo('See normalized metatags in response.');
    $I->seeResponseJsonMatchesJsonPath('$.data..attributes.metatag_normalized');

    $I->expectTo('See metatag title "Test news | Default" in response that generated by consumers_token module');
    $I->seeResponseContainsJson(['content' => 'Test news | Default', 'name' => 'title']);

    $I->expectTo('See metatag descripotion "TEST DESCRIPTION." in response.');
    $I->seeResponseContainsJson(['content' => 'TEST DESCRIPTION', 'name' => 'description']);

    $I->expectTo('See metatag keywords "TEST KEYWORDS." in response.');
    $I->seeResponseContainsJson(['content' => 'TEST KEYWORDS', 'name' => 'keywords']);
  }

}
