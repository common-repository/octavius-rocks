<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 24.07.18
 * Time: 11:20
 */

declare( strict_types=1 );

use OctaviusRocks\OQL\Arguments;
use OctaviusRocks\OQL\Condition;
use OctaviusRocks\OQL\ConditionSet;
use OctaviusRocks\OQL\Field;
use const OctaviusRocks\OQL\OPERATION_AVG;
use const OctaviusRocks\OQL\OPERATION_MAX;
use OctaviusRocks\Server\Response;
use PHPUnit\Framework\TestCase;

final class OQLTest extends TestCase {

	public function testArgumentsAreEmptyException(): void {
		$arguments = new Arguments();
		$this->expectException( "\OctaviusRocks\OQL\InvalidQueryException" );
		$arguments->get();
	}

	public function testAtLeastOneFieldException(): void {
		$arguments = new Arguments();
		$arguments->limit( 1, 1 );

		$this->expectException( "\OctaviusRocks\OQL\InvalidQueryException" );
		$arguments->get();
	}

	public function testArgumentFields() {
		$field = new Field( FIELD::HITS );
		$this->assertEquals( array( "field" => FIELD::HITS), $field->get() );
		$field->addOperation( FIELD::OPERATION_AVG );
		$field->setAlias( "avg" );
		$this->assertEquals( array(
			"field"      => FIELD::HITS,
			"operations" => array( "avg" ),
			"as"         => "avg",
		), $field->get() );
		$field->addOperation( Field::OPERATION_SUM);
		$this->assertEquals( array(
			"field"      => FIELD::HITS,
			"operations" => array( "avg", "sum" ),
			"as"         => "avg",
		), $field->get() );
	}

	public function testArgumentUnknownOperationException() {
		$this->expectException( "\OctaviusRocks\OQL\InvalidQueryException" );
		$field = new Field( "unknown");
		$field->addOperation( Field::OPERATION_AVG );
	}

	public function testArgumentMissingAliasWithOperationsException() {
		$this->expectException( "\OctaviusRocks\OQL\InvalidQueryException" );
		$field = new Field( "myField" );
		$field->addOperation( Field::OPERATION_MAX );
		$field->get();
	}

	public function testConditionSet() {

		//		Arguments::builder()->setConditions()
		$builder = Arguments::builder();
		$builder->addField($builder->buildField("hits"));
		$builder->buildField(FIELD::HITS);

		$request = new \OctaviusRocks\Server\Request();
		$response = $request->post("http://octavius.loca/v562/query", $builder->get());
		$response->getPayload("");




		$set = ConditionSet::builder()
		                   ->addCondition( Condition::builder( FIELD::HITS, "value" ) )
		                   ->useAnd();
		$this->assertEquals( array(
			"entries"  => array(
				array(
					"field"   => FIELD::HITS,
					"value"   => "value",
					"compare" => "=",
				)
			),
			"relation" => "AND",
		), $set->get() );
	}


}

