<?php
interface Search {
    public function searchUsing($_criteria);
    public function numberOfRows();
    public function getNextOffset();
    public function getNextOffsetOf($_n);
    public function getLimit();
    public function setLimit($_limit);
}
?>
