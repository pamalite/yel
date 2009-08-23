<?php
/**
 * This configuration file can be modified at will by adding
 * more countries. 
 * The income ranges are customizable to suit different countries'
 * living standards and currency units. 
 */
 
$default_income_ranges = array('high_level_start' => 15001, 
                               'high_level_end' => -1, 
                               'mid_level_start' => 8001, 
                               'mid_level_end' => 15000, 
                               'entry_level_start' => -1, 
                               'entry_level_end' => 8000 );

$malaysian_income_ranges = array('high_level_start' => 15001, 
                                 'high_level_end' => -1, 
                                 'mid_level_start' => 8001, 
                                 'mid_level_end' => 15000, 
                                 'entry_level_start' => -1, 
                                 'entry_level_end' => 8000 );

$singaporean_income_ranges = array('high_level_start' => 15001, 
                                   'high_level_end' => -1, 
                                   'mid_level_start' => 8001, 
                                   'mid_level_end' => 15000, 
                                   'entry_level_start' => -1, 
                                   'entry_level_end' => 8000 );

$australian_income_ranges = array('high_level_start' => 15001, 
                                  'high_level_end' => -1, 
                                  'mid_level_start' => 8001, 
                                  'mid_level_end' => 15000, 
                                  'entry_level_start' => -1, 
                                  'entry_level_end' => 8000 );

$monthly_income_ranges = array('default' => $default_income_ranges, 
                               'MY' => $malaysian_income_ranges, 
                               'SG' => $singaporean_income_ranges, 
                               'AU' => $australian_income_ranges);
?>
