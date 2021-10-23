<?php declare(strict_types=1);

namespace TgDatabase;

use PHPUnit\Framework\TestCase;

/**
 * Tests the DAO class.
 * @author ralph
 *
 */
final class DaoTest extends TestCase {

    public function testCreateQuery(): void {
		$dao = TestHelper::getDao();
		if ($dao != NULL) {
			$query = $dao->createQuery();
            $this->assertEquals('SELECT * FROM `dual`', $query->getSelectSql());
		}
    }

    public function testCreateQueryCompatible(): void {
		$dao = TestHelper::getDao();
		if ($dao != NULL) {
			$query = $dao->createQuery(NULL, array('attr1' => 'value1', 'attr2' => 'value2'), array('attr3', 'attr4 DESC'), 3, 100);
            $this->assertEquals('SELECT * FROM `dual` WHERE ((`attr1` = \'value1\') AND (`attr2` = \'value2\')) ORDER BY `attr3`,`attr4` DESC LIMIT 100 OFFSET 3', $query->getSelectSql());
		}
    }


    public function testCreateQueryCompatibleAlias(): void {
		$dao = TestHelper::getDao();
		if ($dao != NULL) {
			$query = $dao->createQuery('a', array('attr1' => 'value1', 'attr2' => 'value2'), array('attr3', 'attr4 DESC'), 3, 100);
            $this->assertEquals('SELECT `a`.* FROM `dual` AS `a` WHERE ((`a`.`attr1` = \'value1\') AND (`a`.`attr2` = \'value2\')) ORDER BY `a`.`attr3`,`a`.`attr4` DESC LIMIT 100 OFFSET 3', $query->getSelectSql());
		}
    }

    public function testCreateQueryNew(): void {
		$dao = TestHelper::getDao();
		if ($dao != NULL) {
			$query = $dao->createQuery(NULL, array(Restrictions::eq('attr1','value1'), Restrictions::eq('attr2','value2')), array(Order::asc('attr3'), Order::desc('attr4')), 3, 100);
            $this->assertEquals('SELECT * FROM `dual` WHERE ((`attr1` = \'value1\') AND (`attr2` = \'value2\')) ORDER BY `attr3`,`attr4` DESC LIMIT 100 OFFSET 3', $query->getSelectSql());
		}
    }


}