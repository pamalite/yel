<?php
interface Model {
    public function create($_data);
    public function update($_data);
    public function delete();
    public function get();
    public function find($_criteria);
}
?>
