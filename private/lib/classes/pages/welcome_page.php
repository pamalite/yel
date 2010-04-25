<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). '/../htmltable.php';

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
        $criteria = array(
            'columns' => "jobs.id AS job_id, jobs.title AS position_title, jobs.salary AS salary_start, 
                          jobs.salary_end AS salary_end, jobs.potential_reward AS potential_reward, 
                          branches.currency, employers.name AS employer", 
            'joins' => "job_index ON job_index.job = jobs.id, 
                        employers ON employers.id = jobs.employer, 
                        branches ON branches.id = employers.branch", 
            //'match' => "jobs.closed = 'N' AND jobs.expire_on >= NOW()", 
            'order' => "jobs.salary DESC", 
            'limit' => "10"
        );
        
        $job = new Job();
        $result = $job->find($criteria);
        
        if (count($result) > 0) {
            $top_jobs_table = new HTMLTable('top_jobs_table', '');

            $top_jobs_table->set(0, 0, "Job", '', 'header');
            $top_jobs_table->set(0, 1, "Employer", '', 'header');
            $top_jobs_table->set(0, 2, "Salary Range", '', 'header actions');
            $top_jobs_table->set(0, 3, "Potential Reward", '', 'header actions');

            foreach ($result as $i=>$job) {
                $top_jobs_table->set($i+1, 0, '<a href="job/'. $job['job_id']. '">'. $job['position_title']. '</a>', '', '');
                $top_jobs_table->set($i+1, 1, $job['employer'], '', '');

                $salary = $job['currency']. '$ '. number_format($job['salary_start'], 0, '.', ',');
                if (!is_null($job['salary_end'])) {
                    $salary .= ' - '. number_format($job['salary_start'], 0, '.', ',');
                }
                $top_jobs_table->set($i+1, 2, $salary, '', '');

                $top_jobs_table->set($i+1, 3, $job['currency']. '$ '. number_format($job['potential_reward'], 0, '.', ','), '', '');
            }

            echo $top_jobs_table->get_html();
        } 
    }
    
    private function get_employers() {
        $criteria = array(
            'columns' => 'DISTINCT employers.id, employers.name', 
            'joins' => 'jobs ON employers.id = jobs.employer',
            // 'match' => "jobs.expire_on >= CURDATE() AND jobs.closed = 'N'", 
            'order' => 'employers.name ASC'
        );
        $employer = new Employer();
        $employers = $employer->find($criteria);
        if ($employers === false) {
            $employers = array();
        }
        
        return $employers;
    }
    
    private function get_industries() {
        $industries = array();
        $main_industries = Industry::getMain(true);
        $i = 0;
        foreach ($main_industries as $main) {
            $industries[$i]['id'] = $main['id'];
            $industries[$i]['name'] = $main['industry'];
            $industries[$i]['job_count'] = $main['job_count'];
            $industries[$i]['is_main'] = true;
            $subs = Industry::getSubIndustriesOf($main['id'], true);
            foreach ($subs as $sub) {
                $i++;

                $industries[$i]['id'] = $sub['id'];
                $industries[$i]['name'] = $sub['industry'];
                $industries[$i]['job_count'] = $sub['job_count'];
                $industries[$i]['is_main'] = false;
            }
            $i++;
        }
        
        return $industries;
    }
    
    public function show() {
        $this->begin();
        $this->top_welcome();
        
        $employers = $this->get_employers();
        $industries = $this->get_industries();
        
        $country = $_SESSION['yel']['country_code'];
        if (isset($_SESSION['yel']['member']) &&
            !empty($_SESSION['yel']['member']['id']) && 
            !empty($_SESSION['yel']['member']['sid']) && 
            !empty($_SESSION['yel']['member']['hash'])) {
            $member = new Member($_SESSION['yel']['member']['id']);
            $country = $member->getCountry();
        }
        
        ?>
        <div class="introduction_panels">
            Introduction panels goes here.
            <div style="float: left; width: 24%; height: 80%; border: 1px solid white;">&nbsp;</div>
            <div style="float: left; width: 24%; height: 80%; border: 1px solid white;">&nbsp;</div>
            <div style="float: left; width: 24%; height: 80%; border: 1px solid white;">&nbsp;</div>
            <div style="float: left; width: 24%; height: 80%; border: 1px solid white;">&nbsp;</div>
        </div>
        
        <div class="search">
            <form method="post" action="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/search.php" onSubmit="return verify();">
                <select id="employer" name="employer">
                    <option value="0">Any Employer</option>
                    <option value="0" disabled>&nbsp;</option>
                    <?php
                    foreach ($employers as $emp) {
                    ?>
                    <option value="<?php echo $emp['id'] ?>">
                        <?php echo desanitize($emp['name']); ?>
                    </option>
                    <?php
                    }
                    ?>
                </select>
                <select id="industry" name="industry">
                    <option value="0">Any Specialization</option>
                    <option value="0" disabled>&nbsp;</option>
                    <?php
                    foreach ($industries as $industry) {
                        if ($industry['is_main']) {
                            echo '<option value="'. $industry['id']. '" class="main_industry">';
                            echo $industry['name'];
                        } else {
                            echo '<option value="'. $industry['id']. '">';
                            echo '&nbsp;&nbsp;&nbsp;&nbsp;'. $industry['name'];
                        }

                        if ($industry['job_count'] > 0) {
                            echo '&nbsp;('. $industry['job_count']. ')';
                        }
                        echo '</option>'. "\n";
                    }
                    ?>
                </select><br/>
                <input type="text" name="keywords" id="keywords" value="" alt="Job title or keywords" />
                <input type="submit" value="Search Jobs" /><br/>
                <input type="radio" id="local" name="is_local" value="1" checked />
                <label class="scope" for="local">local jobs</label>
                &nbsp;
                <input type="radio" id="international" name="is_local" value="0" />
                <label class="scope" for="international">international jobs</label>
            </form>
        </div>
        
        <div class="top_jobs">
            <?php $this->generate_top_jobs() ?>
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
                                <a href="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/search.php?industry=0&employer=MATTEL_M&keywords=">
                                    <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/mattel.jpg" alt="Mattel" style="vertical-align: middle;" />
                                </a>
                                &nbsp;&nbsp;&nbsp;
                                <a href="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/search.php?industry=0&employer=wdc_m&keywords=" >
                                    <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/wd.jpg" alt="Western Digital" style="vertical-align: middle;" />
                                </a>
                                &nbsp;&nbsp;&nbsp;
                                <a href="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/search.php?industry=0&employer=digi&keywords=" >
                                    <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/digi.jpg" alt="digi" style="vertical-align: middle;" />
                                </a>
                                &nbsp;&nbsp;&nbsp;
                                <a href="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/search.php?industry=0&employer=altera&keywords=" >
                                    <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/altera.jpg" alt="altera" style="vertical-align: middle;" />
                                </a>
                                &nbsp;&nbsp;&nbsp;
                                <a href="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/search.php?industry=0&employer=entegris&keywords=" >
                                    <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/entegris.jpg" alt="entegris" style="width: 105px; height: 93px; vertical-align: middle;" />
                                </a>
                                &nbsp;&nbsp;&nbsp;
                                <a href="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/search.php?industry=0&employer=nuskin&keywords=" >
                                    <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/nuskin.jpg" alt="nuskin" style="width: 105px; height: 93px; vertical-align: middle;" />
                                </a>
                            </div>
                            <div class="employer_logos" id="employers_1">
                                <a href="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/search.php?industry=0&employer=rstn_m&keywords=" >
                                    <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/rstn.jpg" alt="RSTN" style="vertical-align: middle;" />
                                </a>
                                &nbsp;&nbsp;&nbsp;
                                <a href="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/search.php?industry=0&employer=elawyer&keywords=" >
                                    <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/el.jpg" alt="elawyers" style="width: 105px; height: 93px; vertical-align: middle;" />
                                </a>
                                &nbsp;&nbsp;&nbsp;
                                <a href="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/search.php?industry=0&employer=exabytes&keywords=" >
                                    <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/ex.jpg" alt="Exabytes" style="width: 105px; vertical-align: middle;" />
                                </a>
                                &nbsp;&nbsp;&nbsp;
                                <a href="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/search.php?industry=0&employer=dsem&keywords=" >
                                    <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/dsem.jpg" alt="dsem" style="width: 105px; height: 93px; vertical-align: middle;" />
                                </a>
                                &nbsp;&nbsp;&nbsp;
                                <a href="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/search.php?industry=0&employer=silterra&keywords=" >
                                    <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/silterra.jpg" alt="silterra" style="width: 105px; height: 93px; vertical-align: middle;" />
                                </a>
                                &nbsp;&nbsp;&nbsp;
                                <a href="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/search.php?industry=0&employer=ESS&keywords=" >
                                    <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/essence.jpg" alt="Essence" style="width: 105px; height: 93px; vertical-align: middle;" />
                                </a>
                            </div>
                        </td>
                        <td width="23"><a id="toggle_right" class="no_link"><img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/nav-logo-right.jpg" width="23" height="134" class="prev" /></a></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <?php
    }
}
?>
