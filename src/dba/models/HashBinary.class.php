<?php

namespace DBA;

class HashBinary extends AbstractModel {
  private $hashBinaryId;
  private $hashlistId;
  private $essid;
  private $hash;
  private $plaintext;
  private $timeCracked;
  private $chunkId;
  private $isCracked;
  
  function __construct($hashBinaryId, $hashlistId, $essid, $hash, $plaintext, $timeCracked, $chunkId, $isCracked) {
    $this->hashBinaryId = $hashBinaryId;
    $this->hashlistId = $hashlistId;
    $this->essid = $essid;
    $this->hash = $hash;
    $this->plaintext = $plaintext;
    $this->timeCracked = $timeCracked;
    $this->chunkId = $chunkId;
    $this->isCracked = $isCracked;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['hashBinaryId'] = $this->hashBinaryId;
    $dict['hashlistId'] = $this->hashlistId;
    $dict['essid'] = $this->essid;
    $dict['hash'] = $this->hash;
    $dict['plaintext'] = $this->plaintext;
    $dict['timeCracked'] = $this->timeCracked;
    $dict['chunkId'] = $this->chunkId;
    $dict['isCracked'] = $this->isCracked;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "hashBinaryId";
  }
  
  function getPrimaryKeyValue() {
    return $this->hashBinaryId;
  }
  
  function getId() {
    return $this->hashBinaryId;
  }
  
  function setId($id) {
    $this->hashBinaryId = $id;
  }
  
  function getHashlistId(){
    return $this->hashlistId;
  }
  
  function setHashlistId($hashlistId){
    $this->hashlistId = $hashlistId;
  }
  
  function getEssid(){
    return $this->essid;
  }
  
  function setEssid($essid){
    $this->essid = $essid;
  }
  
  function getHash(){
    return $this->hash;
  }
  
  function setHash($hash){
    $this->hash = $hash;
  }
  
  function getPlaintext(){
    return $this->plaintext;
  }
  
  function setPlaintext($plaintext){
    $this->plaintext = $plaintext;
  }
  
  function getTimeCracked(){
    return $this->timeCracked;
  }
  
  function setTimeCracked($timeCracked){
    $this->timeCracked = $timeCracked;
  }
  
  function getChunkId(){
    return $this->chunkId;
  }
  
  function setChunkId($chunkId){
    $this->chunkId = $chunkId;
  }
  
  function getIsCracked(){
    return $this->isCracked;
  }
  
  function setIsCracked($isCracked){
    $this->isCracked = $isCracked;
  }

  const HASH_BINARY_ID = "hashBinaryId";
  const HASHLIST_ID = "hashlistId";
  const ESSID = "essid";
  const HASH = "hash";
  const PLAINTEXT = "plaintext";
  const TIME_CRACKED = "timeCracked";
  const CHUNK_ID = "chunkId";
  const IS_CRACKED = "isCracked";
}
