<?php
require_once dirname(__FILE__). "/../../utilities.php";

class WelcomePage extends Page {
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_welcome_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/welcome.css">'. "\n";
    }
    
    public function insert_welcome_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/welcome.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        // TODO: Any inline scripts for welcome page goes here.
    }
    
    private function generate_top_jobs() {
        $query = "SELECT jobs.id AS job_id, jobs.title AS position_title, jobs.salary AS salary_start, 
                  jobs.salary_end AS salary_end, jobs.potential_reward AS potential_reward, 
                  currencies.symbol AS currency, employers.name AS employer
                  FROM jobs 
                  LEFT JOIN job_index ON job_index.job = jobs.id 
                  LEFT JOIN employers ON employers.id = jobs.employer 
                  LEFT JOIN currencies ON currencies.country_code = employers.country 
                  WHERE jobs.closed = 'N' AND jobs.expire_on >= NOW() AND 
                  jobs.id NOT IN (SELECT DISTINCT job FROM job_extensions) 
                  ORDER BY jobs.created_on DESC, jobs.potential_reward DESC LIMIT 10";
        $mysql = Database::connect();
        $result = $mysql->query($query);
        
        ?>
    <table class="top_jobs">
        <?php
        if (count($result) > 0 && !is_null($result)) {
            ?>
        <tr>
            <td class="top_jobs_header">Job</td>
            <td class="top_jobs_header">Employer</td>
            <td class="top_jobs_header">Salary Range</td>
            <td class="top_jobs_header">Potential Rewards</td>
        </tr>
            <?php
            $odd = false;
            foreach ($result as $job) {
                if ($odd) {
                    ?>
        <tr>
                    <?php
                    $odd = false;
                } else {
                    ?>
        <tr style="background-color: #EEEEEE;">
                    <?php
                    $odd = true;
                }
                ?>
            <td class="top_jobs_item">
                <a href="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/job/<?php echo $job['job_id']; ?>"><?php echo $job['position_title']; ?></a>
            </td>
            <td class="top_jobs_item"><?php echo $job['employer']; ?></td>
            <td class="top_jobs_item">
                <?php 
                    if ($job['salary_end'] <= 0) {
                        echo 'from '. $job['currency']. ' '. number_format($job['salary_start'], 2, '.', ', '); 
                    } else {
                        echo $job['currency']. ' '. number_format($job['salary_start'], 2, '.', ', '). ' - '. number_format($job['salary_end'], 2, '.', ', '); 
                    }
                ?>
                </td>
            <td class="top_jobs_item">
                <?php echo $job['currency']. ' '. number_format($job['potential_reward'], 2, '.', ', '); ?>
            </td>
        </tr>
                <?php
            }
        } else {
            ?>
        <tr>
            <td class="top_jobs_item" style="text-align: center;">No open jobs at the moment.</td>
        </tr>
            <?php
        }
        ?>
    </table>
        <?php
    }
    
    public function show() {
        $this->begin();
        $this->top_welcome();
        ?>
        <div id="splash_banner" class="splash_banner">
            <a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/tour.php">
                <div id="splash_floater"></div>
            </a>
            <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" class="splash_banner">
                <param name="movie" value="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/flash/top_banner.swf" />
                <param name="allowScriptAccess" value="sameDomain" />
                <param name="quality" value="high" />
                <param name="wmode" value="transparent" />
                <embed src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/flash/top_banner.swf" quality="high"type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" wmode="transparent" class="splash_banner" allowScriptAccess="sameDomain" />
            </object><br/>
            <span style="font-size: 9pt;"><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/tour.php">Click here to Take a Tour</a></span>
        </div>
        <div class="action_buttons">
            <table class="action_buttons">
                <tr>
                    <td id="contact_us" class="action_button">
                        <a class="no_link" onClick="show_contact_drop_form();">
                            <div class="button_floater"></div>
                        </a>
                        <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" class="action_button">
                            <param name="movie" value="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/flash/employers.swf" />
                            <param name="allowScriptAccess" value="sameDomain" />
                            <param name="quality" value="high" />
                            <param name="wmode" value="transparent" />
                            <embed src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/flash/employers.swf" quality="high"type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" class="action_button" wmode="transparent" allowScriptAccess="sameDomain"/>
                        </object>
                        <span style="font-size: 9pt;"><a class="no_link" onClick="show_contact_drop_form();">Click here to Drop Us Your Contact</a></span>
                    </td>
                    <td id="sign_up" class="action_button">
                        <a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/members/sign_up.php">
                            <div class="button_floater"></div>
                        </a>
                        <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" class="action_button">
                            <param name="movie" value="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/flash/member.swf" />
                            <param name="allowScriptAccess" value="sameDomain" />
                            <param name="quality" value="high" />
                            <param name="wmode" value="transparent" />
                            <embed src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/flash/member.swf" quality="high"type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" class="action_button" allowScriptAccess="sameDomain" wmode="transparent" />
                        </object>
                        <span style="font-size: 9pt;"><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/members/sign_up.php">Click here to Sign Up</a></span>
                    </td>
                </tr>
            </table>
        </div>
        <div id="promotion_banner" class="promotion_banner">
            <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" class="promotion_banner">
                <param name="movie" value="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/flash/bonus.swf" />
                <param name="allowScriptAccess" value="sameDomain" />
                <param name="quality" value="high" />
                <param name="wmode" value="transparent" />
                <embed src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/flash/bonus.swf" quality="high"type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" class="promotion_banner" wmode="transparent" allowScriptAccess="sameDomain" />
            </object>
        </div>
        <div class="top_jobs">
            <div style="color: #0071BC; font-weight: bold; font-size: 12pt; padding-top: 20px; padding-bottom: 10px; text-align: center;">Top Jobs</div>
            <?php echo $this->generate_top_jobs() ?>
        </div>
        <div class="rewards" id="total_potential_rewards">
            Loading potential rewards...
        </div>
        <div class="top_employers">
            <div style="color: #666666; font-weight: bold; font-size: 10pt; padding-top: 20px; padding-bottom: 10px; text-align: center;">Who uses Yellow Elevator?</div>
            <table id="table_top_employers" class="top_employers">
                <tr>
                    <td class="arrow" id="td_toggle_left"><a id="toggle_left" class="no_link">&lt;&lt;</a></td>
                    <td id="employer_tabs">
                        <div class="employer_logos" id="employers_0">
                            <a href="http://www.mattel.com" target="_new">
                                <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/mattel.jpg" alt="Mattel" style="vertical-align: middle;" />
                            </a>
                            &nbsp;&nbsp;&nbsp;
                            <a href="http://www.wdc.com" target="_new">
                                <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/wd.jpg" alt="Western Digital" style="vertical-align: middle;" />
                            </a>
                            &nbsp;&nbsp;&nbsp;
                            <a href="http://www.digi.com.my" target="_new">
                                <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/digi.jpg" alt="digi" style="vertical-align: middle;" />
                            </a>
                            &nbsp;&nbsp;&nbsp;
                            <a href="http://www.altera.com" target="_new">
                                <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/altera.jpg" alt="digi" style="vertical-align: middle;" />
                            </a>
                            &nbsp;&nbsp;&nbsp;
                            <a href="http://www.exabytes.com.my" target="_new">
                                <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/ex.jpg" alt="Exabytes" style="width: 105px; vertical-align: middle;" />
                            </a>
                            &nbsp;&nbsp;&nbsp;
                            <a href="http://www.dsem.com" target="_new">
                                <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/dsem.jpg" alt="dsem" style="width: 105px; height: 93px; vertical-align: middle;" />
                            </a>
                        </div>
                        <div class="employer_logos" id="employers_1">
                            <a href="http://www.rstn.com" target="_new">
                                <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/rstn.jpg" alt="RSTN" style="vertical-align: middle;" />
                            </a>
                            &nbsp;&nbsp;&nbsp;
                            <a href="http://www.elawyer.com.my" target="_new">
                                <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/el.jpg" alt="elawyers" style="width: 105px; height: 93px; vertical-align: middle;" />
                            </a>
                        </div>
                    </td>
                    <td class="arrow" id="td_toggle_right"><a id="toggle_right" class="no_link">&gt;&gt;</a></td>
                </tr>
            </table>
        </div><br/>
        
        <div id="div_blanket"></div>
        <div id="div_contact_drop_form">
            <form method="post" id="contact_drop_form" onSubmit="return false;">
                <table class="drop_contact">
                    <tr>
                        <td colspan="2" class="title">Drop Us Your Contact</td>
                    </tr>
                    <tr>
                        <td class="label"><label for="company">Company Name:</label></td>
                        <td><input type="text" id="company" name="company" value=""></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="phone">Contact Number:</label></td>
                        <td><input type="text" id="phone" name="phone" value=""></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="email">E-mail Address:</label></td>
                        <td><input type="text" id="email" name="email" value=""></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="contact">Contact Person:</label></td>
                        <td><input type="text" id="contact" name="contact" value=""></td>
                    </tr>
                    <tr>
                        <td class="buttons" colspan="2">
                            <input type="button" class="drop" onClick="close_contact_drop_form();" value="Cancel">
                            &nbsp;
                            <input type="button" class="drop" id="drop" value="Drop My Contact Now">
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        
        <?php
    }
}
?>
