<?php
require_once dirname(__FILE__). "/../../utilities.php";

class AdvisoryPage extends Page {
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_advisory_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/about.css">'. "\n";
    }
    
    public function insert_advisory_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/advisory.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        // TODO: Any inline scripts for welcome page goes here.
    }
    
    public function show() {
        $this->begin();
        $this->top("Yelow Elevator&nbsp;&nbsp;<span style=\"color: #FC8503;\">Members of the Advisory Board</a>");
        ?>
        <div class="content">
            <div class="advisory_member">
                <span id="advisory_member_name">Dato' Hari Das Nair</span> graduated with a Bachelor of Arts majoring in Economics from University of Malaya. He also holds a Diploma in Education from Singapore University and Diploma in Institute of Banking, UK. Dato' Hari started his career as a teacher in 1973 and then pursued a career in banking with HSBC Bank Malaysia Berhad. He has held senior positions in the bank and has served in different capacities. His last two positions before his retirement were Area Manager of HSBC Bank in the Northern Region and Director of Strategic Business Development.  Having served HSBC for 33 years Dato' Hari comes with vast experience in people management skills, business development strategies and service enhancement tactics. 
            </div>
            <br />
            <div class="advisory_member">
                <span id="advisory_member_name">Dato' Robin Seo</span> has 30 years of industrial experience working with Multinational companies in the Electronic and Communication Industries. Dato' Robin's last position was the Country President for Motorola in Malaysia and the Vice President and Managing Director of Motorola Penang.  Dato' Robin has a Bachelor of Engineering (Mechanical) degree from the University of Melbourne, Australia and a Masters in Business Administration from Nova University, Florida, USA. Robin has over the years contributed immensely in many Federal and Penang State initiatives. These include his roles as:
                <ul>
                    <li>Chairman of the Penang Skills Development Center (PSDC) Management Council.</li>
                    <li>Board Governor of American Chamber of Commerce (Amcham)</li>
                    <li>Main committee member of the Penang Competitiveness Council (PECO), and Penang K-ICT Council </li>
                    <li>Chairman of the Connectivity Steering Committee in Penang K-ICT Council</li>
                    <li>Chairman of Penang Broadband Stakeholders Group</li>
                </ul>
                He currently sits as Independent Non-Executive Director in the Board of LKT Industrial Sdn Bhd, a local company engaging in design and manufacture automatic assembly and test equipments for the semiconductor, electronic and telecommunication industries.
            </div>
            <br />
            <div class="advisory_member">
                <span id="advisory_member_name">Dato' Wong Siew Hai</span> has 29 years of experience in the electronics industry. He retired from Intel after 27 years of service. His last position with Intel was Vice President of Technology and Manufacturing Group (TMG) and General Manager of Assembly and Test Manufacturing (ATM), responsible for all assembly test factories world wide. Also, he was the Vice President &amp; Managing Director of Dell's Asia Pacific Customer Center for about 2 years. He holds a Bachelor of Science degree in Mechanical Engineering, University of Leeds, UK and a Masters in Management Science degree from the Imperial College of Science and Technology, University of London, UK. 
            </div>
            <br />
            <div class="advisory_member">
                <span id="advisory_member_name">Dr. Tan Kim Hor</span> has 18 years of experience in construction, commissioning and maintenance of an integrated steel plant and cement plant. His last position was the CEO of a public-listed conglomerate in Singapore involved in quarries, cement plants, ready-mixed concrete, infrastructure construction and property development. He was once the CEO of a German glass factory, the COO and project manager of a cement plant, and was an engineer of Rolling Mill, Arc Furnace and Blast Furnace. Dr. Tan holds a Bachelor of Engineering (Hons) degree, a Master of Business Administration (MBA), and a Doctor of Business Administration (DBA).
            </div>
        </div>
        <?php
    }
}
?>