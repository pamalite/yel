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
                  ORDER BY jobs.salary DESC LIMIT 10";
        $mysql = Database::connect();
        $result = $mysql->query($query);
        
        ?>
    <table border="0" cellspacing="0" cellpadding="0" class="border">
        <tr>
          <td class="table-tl"></td>
          <td class="table-tc"></td>
          <td class="table-tr"></td>
        </tr>
        <tr>
          <td class="table-cl"></td>
          <td class="data">
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
                        echo 'from '. $job['currency']. ' '. number_format($job['salary_start'], 0, '.', ', '); 
                    } else {
                        echo $job['currency']. ' '. number_format($job['salary_start'], 0, '.', ', '). ' - '. number_format($job['salary_end'], 0, '.', ', '); 
                    }
                ?>
                </td>
            <td class="top_jobs_item">
                <?php 
                    $total_potential_reward = $job['potential_reward'];
                    $potential_token_reward = $total_potential_reward * 0.05;
                    $potential_reward = $total_potential_reward - $potential_token_reward;
                    echo $job['currency']. ' '. number_format($potential_reward, 0, '.', ', '); 
                ?>
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
          </td>
          <td class="table-cr"></td>
        </tr>
        <tr>
          <td class="table-bl"></td>
          <td class="table-bc"></td>
          <td class="table-br"></td>
        </tr>
    </table>
        <?php
    }
    
    public function show() {
        $this->begin();
        $this->top_welcome();
        ?>
        <div id="search_info">
            <div class="job_search">
                <form method="post" action="search.php" onSubmit="return verify();">
                    <div class="search_form">
                        <span id="employer_drop_down"></span>
                        <span id="industry_drop_down"></span><br/>
                        <div style="padding-top: 5px;">
                            <input type="text" name="keywords" id="keywords" value="Job title or keywords">
                            <!--input id="search_button" type="submit" value="Search"-->
                            <input type="image" name="submit" id="search_button" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/but-search.gif" value="Search" />
                        </div>
                    </div>
                </form>
            </div>
            <div class="intro" style="font-size: 8pt;"> While you search for mid-high level job opportunities for yourself, you can also refer jobs to your contacts and earn rewards for every successful referral. Furthermore, we will contact you directly whenever there are job opportunities that match your resume. So sign up and upload your resume today!</div>
            <a href="members/sign_up.php" class="takeatour"><img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/sign_up_upload_resume.gif" /></a>
        </div>
        <div id="action_buttons_jasmine">
            <div class="box employer">
                <img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/but-be-emp.gif" width="257" height="28" class="title" />
                <div class="descr">
                    &gt; Better Screened Candidates<br/>
                    &gt; Faster Turn Around Time<br/>
                    &gt; <strong>Free Job Postings</strong><br/>
                    &gt; <strong>Free Registration</strong><br/>
                </div>
                <a href="#" class="signup" onClick="show_contact_drop_form();"><img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/contact_sign_up.jpg" /></a>
            </div>
            <div class="box member"><img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/but-be-mem.gif" width="257" height="28" class="title" />
                <div class="descr">
                    &gt; Access To Mid-High Level Job Opportunities<br/>
                    &gt; Greater Rewards For Every Successful Referral<br/>
                    &gt; Effective Tracking Of Job Applications<br/>
                    &gt; Bonus if you are hired<br/>
                    &gt; <strong>Free Membership</strong><br/>
                </div>
                <a href="members/sign_up.php" class="signup2"><img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/click_sign_up.jpg" /></a>
            </div>
        </div>
        <div id="sign_up_banner">
            <a href="members/sign_up.php"><img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/bigtitle.gif" /></a>
        </div>
        
        <div id="top_jobs">
            <img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/topjobs.gif" width="326" height="46" class="topjobs" />
            <?php echo $this->generate_top_jobs() ?>
        </div>
        
        <div class="rewards" id="total_potential_rewards">
            Loading potential rewards...
        </div>
        
        <div id="top_employers">
            <div id="employers_carousel">
                <table border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td colspan="3"><img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/head-whousesye.jpg" width="328" height="46" style="margin-left: 45px; vertical-align: bottom;" /></td>
                    </tr>
                    <tr>
                        <td width="23"><a id="toggle_left" class="no_link"><img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/nav-logo-left.jpg" width="23" height="134" class="prev" /></a></td>
                        <td class="nav-center" id="employer_tabs">
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
                                <a href="http://www.entegris.com" target="_new">
                                    <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/entegris.jpg" alt="entegris" style="width: 105px; height: 93px; vertical-align: middle;" />
                                </a>
                                &nbsp;&nbsp;&nbsp;
                                <a href="http://www.nuskin.com" target="_new">
                                    <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/nuskin.jpg" alt="nuskin" style="width: 105px; height: 93px; vertical-align: middle;" />
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
                                &nbsp;&nbsp;&nbsp;
                                <a href="http://www.exabytes.com.my" target="_new">
                                    <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/ex.jpg" alt="Exabytes" style="width: 105px; vertical-align: middle;" />
                                </a>
                                &nbsp;&nbsp;&nbsp;
                                <a href="http://www.dsem.com" target="_new">
                                    <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/dsem.jpg" alt="dsem" style="width: 105px; height: 93px; vertical-align: middle;" />
                                </a>
                            </div>
                        </td>
                        <td width="23"><a id="toggle_right" class="no_link"><img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/nav-logo-right.jpg" width="23" height="134" class="prev" /></a></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div style="width: 100%; margin:auto; text-align: center; padding-top: 30px;">
            <a href="http://twitter.com/yellowelevator" target="_new">
                <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/twitter_button.jpg" />
            </a>
        </div>
        
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
