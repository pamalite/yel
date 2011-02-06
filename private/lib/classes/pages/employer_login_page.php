<?php
require_once dirname(__FILE__). "/../page.php";

class EmployerLoginPage extends Page {
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_employer_login_css() {
        $this->insert_css('employer_login.css');
    }
    
    public function insert_employer_login_scripts() {
        $this->insert_scripts('employer_login.js');
    }
    
    public function insert_inline_scripts() {
        // TODO: Any inline scripts for welcome page goes here.
    }
    
    public function show($_error = "") {
        $this->begin();
        $this->top('Employer Sign In');
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <!-- jasmine start -->
        <div class="content">
            <div class="main">
                <h1><img src="<?php echo $this->url_root; ?>/common/images/login_welcome.jpg" /></h1>
                
                <div class="login-enquiry-box">
                    <span>For enquiries, please email your name, designation, company name, and phone number to: <a href="mailto:sales@yellowelevator.com" style="color: #FDD501;">sales@yellowelevator.com</a> and we will contact you shortly.</span>
                </div>
                
                We like to thank our clients for their support. We have grown & rebranded <br/>
                our company recently. While you enjoy this video, please take note that our<br/>
                current video will be rebranded very soon.<br/><br/>
                <object width="487" height="300"><param name="movie" value="http://www.youtube.com/v/YzfEH0UPEBo?fs=1&amp;hl=en_US"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/YzfEH0UPEBo?fs=1&amp;hl=en_US" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="487" height="300"></embed></object>
            </div>
            <div class="side">
                <div class="login_form">
                    <img src="<?php echo $this->url_root; ?>/common/images/login_signin.jpg" style="margin-bottom:23px;" /><br/>
                    <form method="post" onSubmit="return false;">
                        <label for="id">User ID:</label><br/>
                        <input type="text" id="id" name="id" value="" />
                        <br/><br/>
                        <label for="password">Password:</label><br/>
        
                        <input type="password" id="password" name="password" />
                        <div class="button_bar left">
                            <a href="https://yellowelevator.com/yel/contact.php">Forgot Password? Call Support.</a>
                        </div>
                        <div class="button_bar right">
                            <input type="submit" class="login" id="login" value="Sign In" />
                        </div>
                    </form>
        
                </div>
                <div class="client-box">
                    <img src="<?php echo $this->url_root; ?>/common/images/login_our_client.jpg" style="margin-bottom:23px;" /><br/>
                    <div id="employers_carousel">
                        <table border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="8"><a id="toggle_left" class="no_link"><img src="<?php echo $this->url_root; ?>/common/images/nav-logo-left.jpg" width="8" height="14" class="prev" /></a></td>
                                <td class="nav-center" id="employer_tabs" width="250">
                                    <div class="employer_logos" id="employers_0">
                                        <table cellpadding="0" cellspacing="0" width="230" height="135">
                                            <tr>
                                                <td align="center">
                                                    <a href="https://yellowelevator.com/yel/search.php?industry=0&employer=MATTEL_M&keywords=">
                                                        <img src="<?php echo $this->url_root; ?>/common/images/logos/s_mattel.jpg" alt="Mattel" style="vertical-align: middle;" />
                                                    </a>
                                                </td>
                                                <td align="center">
                                                    <a href="https://yellowelevator.com/yel/search.php?industry=0&employer=wdc_m&keywords=" >
                                                        <img src="<?php echo $this->url_root; ?>/common/images/logos/s_wd.jpg" alt="Western Digital" style="vertical-align: middle;" />
                                                    </a>
                                                </td>
                                                <td align="center">
                                                    <a href="https://yellowelevator.com/yel/search.php?industry=0&employer=digi&keywords=" >
                                                        <img src="<?php echo $this->url_root; ?>/common/images/logos/s_digi.jpg" alt="digi" style="vertical-align: middle;" />
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center">
                                                    <a href="https://yellowelevator.com/yel/search.php?industry=0&employer=altera&keywords=" >
                                                        <img src="<?php echo $this->url_root; ?>/common/images/logos/s_altera.jpg" alt="altera" style="vertical-align: middle;" />
                                                    </a>
                                                </td>
                                                <td align="center">
                                                    <a href="https://yellowelevator.com/yel/search.php?industry=0&employer=entegris&keywords=" >
                                                        <img src="<?php echo $this->url_root; ?>/common/images/logos/s_entegris.jpg" alt="entegris" style="vertical-align: middle;" />
                                                    </a>
                                                </td>
                                                <td align="center">
                                                    <a href="https://yellowelevator.com/yel/search.php?industry=0&employer=nuskin&keywords=" >
                                                        <img src="<?php echo $this->url_root; ?>/common/images/logos/s_nuskin.jpg" alt="nuskin" style="vertical-align: middle;" />
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
        
                                    </div>
                                    <div class="employer_logos" id="employers_1">
                                        <table cellpadding="0" cellspacing="0" width="230" height="135">
                                            <tr>
                                                <td>
                                                    <a href="https://yellowelevator.com/yel/search.php?industry=0&employer=rstn_m&keywords=" >
                                                        <img src="<?php echo $this->url_root; ?>/common/images/logos/s_rstn.jpg" alt="RSTN" style="vertical-align: middle;" />
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="https://yellowelevator.com/yel/search.php?industry=0&employer=elawyer&keywords=" >
                                                        <img src="<?php echo $this->url_root; ?>/common/images/logos/s_el.jpg" alt="elawyers" style="vertical-align: middle;" />
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="https://yellowelevator.com/yel/search.php?industry=0&employer=exabytes&keywords=" >
                                                        <img src="<?php echo $this->url_root; ?>/common/images/logos/s_ex.jpg" alt="Exabytes" style="vertical-align: middle;" />
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <a href="https://yellowelevator.com/yel/search.php?industry=0&employer=dsem&keywords=" >
                                                        <img src="<?php echo $this->url_root; ?>/common/images/logos/s_dsem.jpg" alt="dsem" style="vertical-align: middle;" />
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="https://yellowelevator.com/yel/search.php?industry=0&employer=silterra&keywords=" >
                                                        <img src="<?php echo $this->url_root; ?>/common/images/logos/s_silterra.jpg" alt="silterra" style="vertical-align: middle;" />
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="https://yellowelevator.com/yel/search.php?industry=0&employer=ESS&keywords=" >
                                                        <img src="<?php echo $this->url_root; ?>/common/images/logos/s_essence.jpg" alt="Essence" style="vertical-align: middle;" />
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </td>
        
                                <td width="14"><a id="toggle_right" class="no_link" style="margin-left:6px;"><img src="<?php echo $this->url_root; ?>/common/images/nav-logo-right.jpg" width="8" height="14" class="prev" /></a></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
        </div>
        <!-- jasmine end -->
        
        <!-- popup goes here -->
        <div id="contact_drop_form" class="popup_window">
            <form method="post" onSubmit="return false;">
                <table class="drop_contact">
                    <tr>
                        <td colspan="2" class="title">Drop Us Your Contact</td>
                    </tr>
                    <tr>
                        <td class="label"><label for="company">Company Name:</label></td>
                        <td><input type="text" id="company" name="company" value="" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="phone">Contact Number:</label></td>
                        <td><input type="text" id="phone" name="phone" value="" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="email">E-mail Address:</label></td>
                        <td><input type="text" id="email" name="email" value="" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="contact">Contact Person:</label></td>
                        <td><input type="text" id="contact" name="contact" value="" /></td>
                    </tr>
                    <tr>
                        <td class="buttons" colspan="2">
                            <input type="button" class="drop" id="drop" value="Drop My Contact Now" />
                            <input type="button" class="drop" onClick="close_contact_drop_form();" value="Cancel" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        
        <!--div id="div_blanket"></div>
        <div id="div_contact_drop_form">
            
        </div-->
        <?php
    }
}
?>