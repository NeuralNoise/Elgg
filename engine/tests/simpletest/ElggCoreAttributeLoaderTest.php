<?php
use Elgg\Config;

/**
 * Test attribute loader for entities
 */
class ElggCoreAttributeLoaderTest extends \ElggCoreUnitTest {

	public function up() {
		$types = Config::getEntityTypes();

		foreach ($types as $type) {
			switch ($type) {
				case 'group' :
					$this->entities[] = $this->createGroup();
					break;

				case 'user' :
					$this->entities[] = $this->createUser();
					break;

				case 'object' :
					$this->entities[] = $this->createObject();
					break;
			}
		}

	}

	public function down() {
		foreach ($this->entities as $entity) {
			$entity->delete();
		}
	}

	/**
	 * Checks if additional select columns are readable as volatile data
	 *
	 * https://github.com/Elgg/Elgg/issues/5543
	 */
	public function testSqlAdditionalSelectsAsVolatileData() {

		$types = Config::getEntityTypes();

		foreach ($types as $type) {

			$entities = elgg_get_entities([
				'type' => $type,
				'selects' => ['42 as added_col2'],
				'limit' => 1,
			]);

			$this->assertFalse(empty($entities));

			if ($entities) {
				$entity = array_shift($entities);
				$this->assertTrue($entity instanceof \ElggEntity);
				$this->assertEqual($entity->added_col2, null, "Additional select columns are leaking to attributes for " . get_class($entity));
				$this->assertEqual($entity->getVolatileData('select:added_col2'), 42);
			}
		}

	}

	/**
	 * Checks if additional select columns are readable as volatile data even if we hit the cache while fetching entity.
	 *
	 * https://github.com/Elgg/Elgg/issues/5544
	 */
	public function testSqlAdditionalSelectsAsVolatileDataWithCache() {

		$types = Config::getEntityTypes();

		foreach ($types as $type) {

			$entities = elgg_get_entities([
				'type' => $type,
				'selects' => ['42 as added_col3'],
				'limit' => 1,
			]);

			$this->assertFalse(empty($entities));

			if ($entities) {
				$entity = array_shift($entities);
				$this->assertTrue($entity instanceof \ElggEntity);
				$this->assertEqual($entity->added_col3, null, "Additional select columns are leaking to attributes for " . get_class($entity));
				$this->assertEqual($entity->getVolatileData('select:added_col3'), 42);

				// make sure we have cached the entity
				$this->assertNotEqual(false, _elgg_services()->entityCache->get($entity->guid));
			}
		}

		// run these again but with different value to make sure cache does not interfere
		foreach ($types as $type) {
			$entities = elgg_get_entities([
				'type' => $type,
				'selects' => ['64 as added_col3'],
				'limit' => 1,
			]);

			$this->assertFalse(empty($entities));

			if ($entities) {
				$entity = array_shift($entities);
				$this->assertTrue($entity instanceof \ElggEntity);
				$this->assertEqual($entity->added_col3, null, "Additional select columns are leaking to attributes for " . get_class($entity));
				$this->assertEqual($entity->getVolatileData('select:added_col3'), 64, "Failed to overwrite volatile data in cached entity");
			}
		}
	}

}
