<?php
interface Session {
    public function set($_user_id);
    public function get($_user_id);
    public function reset($_user_id);
    public function isValid($_user_id, $_hash);
    public function generateNewSeed();
    public function getSeedId();    
}
?>