<?php
/**
 * Countries
 *
 * Converts country code to full name in many languages and can
 * provide lots of other useful information.
 *
 * @version    1.2 (2017-05-03 12:58:00 GMT)
 * @author     Peter Kahl <peter.kahl@colossalmind.com>
 * @since      2017
 * @license    Apache License, Version 2.0
 *
 * Copyright 2017 Peter Kahl <peter.kahl@colossalmind.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      <http://www.apache.org/licenses/LICENSE-2.0>
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace peterkahl\Countries;

use \Exception;

class Countries {

  public $dbresource;

  #===================================================================

  private function ValidateLanguage($lang) {
    $available = array(
      'ar',
      'cs',
      'da',
      'de',
      'en',
      'es',
      'fr',
      'he',
      'it',
      'ja',
      'nl',
      'pt',
      'ru',
      'sk',
      'zh-cn',
      'zh-hk',
    );
    $lang = $this->denormaliseLangcode($lang);
    if ($lang == 'zh-tw' || $lang == 'zh-mo') {
      $lang = 'zh-hk';
    }
    if ($lang == 'zh-sg' || $lang == 'zh') {
      $lang = 'zh-cn';
    }
    if (in_array($lang, $available)) {
      return $lang;
    }
    $lang = substr($lang, 0, 2);
    if (in_array($lang, $available)) {
      return $lang;
    }
    throw new Exception('Invalid argument lang');
  }

  #===================================================================

  /**
   * turns en_GB --> en-gb
   *
   */
  private function denormaliseLangcode($str) {
    $str = strtolower($str);
    return str_replace('_', '-', $str);
  }

  #===================================================================

  /**
   * Fetches the name in given language of given country (code).
   * @return string
   */
  public function code2countryName($code, $lang = 'en') {
    $lang = $this->ValidateLanguage($lang);
    $sql = "SELECT `country_".mysqli_real_escape_string($this->dbresource, $lang)."` FROM `countries` WHERE `code`='".mysqli_real_escape_string($this->dbresource, strtolower($code))."';";
    $result = mysqli_query($this->dbresource, $sql);
    if ($result === false) {
      throw new Exception('Error executing SQL query');
    }
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
      mysqli_free_result($result);
      return $row['country_'.$lang];
    }
    throw new Exception('Invalid argument code');
  }

  #===================================================================

  /**
   * Returns array of all world's countries as pairs of
   * code + full name in specified language.
   * This is useful for generating HTML of select (drop-down list)
   * of country names
   * @return array
   */
  public function getAllCodesNames($lang = 'en') {
    $lang = $this->ValidateLanguage($lang);
    $new = array();
    $sql = "SELECT `code`,`country_".mysqli_real_escape_string($this->dbresource, $lang)."` FROM `countries` ORDER BY `country_".mysqli_real_escape_string($this->dbresource, $lang)."` ASC;";
    $result = mysqli_query($this->dbresource, $sql);
    if ($result === false) {
      throw new Exception('Error executing SQL query');
    }
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
      # We ignore these codes that aren't real countries
      if (!in_array($row['code'], array('yu','eu','ap','nt','aq','01'))) {
        $new[] = array('code' => $row['code'], 'name' => $row['country_'.$lang]);
      }
    }
    mysqli_free_result($result);
    unset($row);
    return $new;
  }

  #===================================================================

  /**
   * Fetches the whole row of data for given country (code).
   * Name of country is returned only in specified language.
   * @return array
   */
  public function getCountryInfo($code, $lang = 'en') {
    $lang = $this->ValidateLanguage($lang);
    $sql = "SELECT * FROM `countries` WHERE `code`='".mysqli_real_escape_string($this->dbresource, strtolower($code))."';";
    $result = mysqli_query($this->dbresource, $sql);
    if ($result === false) {
      throw new Exception('Error executing SQL query');
    }
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
      mysqli_free_result($result);
      $row['name'] = $row['country_'.$lang];
      foreach ($row as $key => $val) {
        if (substr($key, 0, 8) == 'country_' && $key != 'country_iso') {
          unset($row[$key]);
        }
      }
      return $row;
    }
    throw new Exception('Invalid argument code');
  }

  #===================================================================

}