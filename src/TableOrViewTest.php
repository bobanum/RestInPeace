use PHPUnit\Framework\TestCase;
use RestInPeace\TableOrView;
use RestInPeace\Database;
use RestInPeace\Relation;

<?php


class TableOrViewTest extends TestCase {
	private $databaseMock;
	private $tableOrView;

	protected function setUp(): void {
		$this->databaseMock = $this->createMock(Database::class);
		$this->tableOrView = new TableOrView($this->databaseMock, 'test_table');
	}

	public function testGetPrimaryKey() {
		$this->databaseMock->method('getPrimaryKey')->willReturn(['id']);
		$primaryKey = $this->tableOrView->get_primary_key();
		$this->assertEquals('id', $primaryKey);
	}

	public function testSetPrimaryKey() {
		$this->tableOrView->set_primary_key(['id']);
		$this->assertEquals('id', $this->tableOrView->get_primary_key());
	}

	public function testIsValid() {
		$this->assertTrue($this->tableOrView->isValid());
	}

	public function testAddRelation() {
		$relatedTable = new TableOrView($this->databaseMock, 'related_table');
		$this->tableOrView->addRelation($relatedTable, 'foreign_key');
		$this->assertArrayHasKey('related_table', $this->tableOrView->relations);
		$this->assertArrayHasKey('test_table', $relatedTable->relations);
	}

	public function testIsJunctionTable() {
		$tableSchema = [
			'columns' => [
				['name' => 'user_id', 'pk' => 0],
				['name' => 'group_id', 'pk' => 0]
			]
		];
		$this->assertTrue(TableOrView::isJunctionTable($tableSchema));
	}

	public function testProcessSuffixedViews() {
		$views = [
			'view1' => $this->createMock(TableOrView::class),
			'view2' => $this->createMock(TableOrView::class)
		];
		$views['view1']->method('get_suffixe')->willReturn('suffix1');
		$views['view2']->method('get_suffixe')->willReturn('suffix2');
		$result = $this->tableOrView->processSuffixedViews($views);
		$this->assertArrayHasKey('suffix1', $result);
		$this->assertArrayHasKey('suffix2', $result);
	}

	public function testGetCols() {
		$_GET['cols'] = 'id,name';
		$cols = TableOrView::getCols();
		$this->assertEquals('`id`,`name`', $cols);
	}

	public function testAddParams() {
		$query = [];
		$_GET['by'] = 'name';
		$_GET['order'] = 'DESC';
		$_GET['limit'] = 10;
		$_GET['offset'] = 5;
		$result = TableOrView::addParams($query);
		$this->assertContains('ORDER BY "name" DESC', $result);
		$this->assertContains('LIMIT 10', $result);
		$this->assertContains('OFFSET 5', $result);
	}

	public function testAll() {
		$this->databaseMock->method('execute')->willReturn([]);
		$result = $this->tableOrView->all();
		$this->assertIsArray($result);
	}

	public function testFind() {
		$this->databaseMock->method('execute')->willReturn(['id' => 1]);
		$result = $this->tableOrView->find(1);
		$this->assertIsArray($result);
	}

	public function testRelated() {
		$relatedTable = new TableOrView($this->databaseMock, 'related_table');
		$relation = new Relation(Relation::BELONGS_TO, $this->tableOrView, $relatedTable, 'foreign_key');
		$this->tableOrView->relations['related_table'] = $relation;
		$this->databaseMock->method('execute')->willReturn([['id' => 1]]);
		$result = $this->tableOrView->related('related_table', 1);
		$this->assertIsArray($result);
	}

	public function testFrom() {
		$config = [
			'name' => 'test_table',
			'columns' => ['id', 'name']
		];
		$result = TableOrView::from($config, $this->databaseMock);
		$this->assertInstanceOf(TableOrView::class, $result);
		$this->assertEquals('test_table', $result->name);
		$this->assertEquals(['id', 'name'], $result->columns);
	}
}