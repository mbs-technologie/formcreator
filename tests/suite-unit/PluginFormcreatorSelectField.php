<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator is a plugin which allows creation of custom forms of
 * easy access.
 * ---------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of Formcreator.
 *
 * Formcreator is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Formcreator. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 *
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorSelectField extends CommonTestCase {

   public function provider() {

      $dataset = [
         [
            'fields'          => [
                  'fieldtype'       => 'select',
                  'name'            => 'question',
                  'required'        => '0',
                  'show_empty'      => '0',
                  'default_values'  => '',
                  'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
                  'order'           => '1',
                  'show_rule'       => 'always'
            ],
            'data'            => null,
            'expectedValue'   => '1',
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
                  'fieldtype'       => 'select',
                  'name'            => 'question',
                  'required'        => '0',
                  'show_empty'      => '1',
                  'default_values'  => '',
                  'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
                  'order'           => '1',
                  'show_rule'       => 'always'
            ],
            'data'            => null,
            'expectedValue'   => '',
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
                  'fieldtype'       => 'select',
                  'name'            => 'question',
                  'required'        => '0',
                  'show_empty'      => '0',
                  'default_values'  => '3',
                  'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
                  'order'           => '1',
                  'show_rule'       => 'always'
            ],
            'data'            => null,
            'expectedValue'   => '3',
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
                  'fieldtype'       => 'select',
                  'name'            => 'question',
                  'required'        => '1',
                  'show_empty'      => '0',
                  'default_values'  => '',
                  'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
                  'order'           => '1',
                  'show_rule'       => 'always'
            ],
            'data'            => null,
            'expectedValue'   => '1',
            'expectedIsValid' => false
         ],
         [
            'fields'          => [
                  'fieldtype'       => 'select',
                  'name'            => 'question',
                  'required'        => '1',
                  'show_empty'      => '1',
                  'default_values'  => '',
                  'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
                  'order'           => '1',
                  'show_rule'       => 'always'
            ],
            'data'            => null,
            'expectedValue'   => '',
            'expectedIsValid' => false
         ],
      ];

      return $dataset;
   }

   /**
    * @dataProvider provider
    */
   public function testFieldAvailableValue($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new \PluginFormcreatorSelectField($fields, $data);

      $availableValues = $fieldInstance->getAvailableValues();
      $expectedAvaliableValues = explode("\r\n", $fields['values']);

      $this->integer(count($availableValues))->isEqualTo(count($expectedAvaliableValues));

      foreach ($expectedAvaliableValues as $expectedValue) {
         $this->array($availableValues)->contains($expectedValue);
      }
   }

   /**
    * @dataProvider provider
    */
   public function testGetValue($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new \PluginFormcreatorSelectField($fields, $data);

      $value = $fieldInstance->getValue();
      $this->string($value)->isEqualTo($expectedValue);
   }

   /**
    * @dataProvider provider
    */
   public function testFieldIsValid($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new \PluginFormcreatorSelectField($fields, $data);

      $isValid = $fieldInstance->isValid($fields['default_values']);
      $this->boolean((boolean) $isValid)->isEqualTo($expectedValidity);
   }

   public function testGetName() {
      $output = \PluginFormcreatorSelectField::getName();
      $this->string($output)->isEqualTo('Select');
   }
}