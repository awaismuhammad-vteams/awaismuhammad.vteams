<?php
session_start();
$uname = '';
$uname = $this->session->userdata('user_name');
if (!empty($uname)) {
    $_SESSION['username'] = $this->session->userdata('user_name'); // Must be already set
}
?>

<!--home start-->

<!-- Main Contnr Start --> 
<div class="mainCintnr">
    <div class="row">
        <div class="large-3 columns">

            <!-- Category Start --> 
            <div class="yloHeading ctgHed">Category</div>
            <div class="whiteBkg">
                <ul class="topnav">
                    <?php
                    if (!empty($getAllMainCateg)) {
                        for ($c = 0; $c < count($getAllMainCateg); $c++) {
                            $cat_id = $getAllMainCateg[$c]->cat_id;
                            $cat_name = $getAllMainCateg[$c]->cat_name;
                            ?>
                            <li ><a href="#"><?php echo $cat_name; ?></a>
                                <ul>
                                    <?php
                                    if (!empty($getAllSubCateg)) {
                                        for ($sc = 0; $sc < count($getAllSubCateg); $sc++) {
                                            $scat_id = $getAllSubCateg[$sc]->cat_id;
                                            $scat_name = $getAllSubCateg[$sc]->cat_name;
                                            $parent_id = $getAllSubCateg[$sc]->parent_id;
                                            if ($parent_id == $cat_id) {
                                                ?>
                                                <li><a href="#"><?php echo $scat_name; ?></a>
                                                    <ul>
                                                        <?php
                                                        for ($sc2 = 0; $sc2 < count($getAllSubCateg); $sc2++) {
                                                            $sub_scat_id = $getAllSubCateg[$sc2]->cat_id;
                                                            $sub_scat_name = $getAllSubCateg[$sc2]->cat_name;
                                                            $sub_parent_id = $getAllSubCateg[$sc2]->parent_id;
                                                            if ($sub_parent_id == $scat_id) {
                                                                ?>

                                                                <li><a href="<?php echo base_url(); ?>category/view/scat/<?php echo $scat_id; ?>"><?php echo $sub_scat_name; ?></a></li>

                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                                    </ul>
                                                </li>
                                                <?php
                                            }
                                        }
                                    }
                                    ?>
                                </ul>
                            </li>
                            <?php
                        }
                    }
                    ?>
                </ul>
            </div>	
            <!-- Category End --> 

            <!-- Best Sellers Start --> 
            <div class="whiteBkg special">
                <div class="yloHeading">Best Sellers</div>
                <ul class="bSelerList">
                    <?php
                    if (!empty($BestSellers)) {
                        for ($s = 0; $s < count($BestSellers); $s++) {
                            $user_photo = $BestSellers[$s]->user_photo;
                            $user_name = $BestSellers[$s]->user_name;
                            $user_id = $BestSellers[$s]->user_id;
                            $user_about = $BestSellers[$s]->user_about;
                            $user_activation_status = $BestSellers[$s]->activation_status;
                            $paid_user_status = $BestSellers[$s]->paid_user_status;
                            if ($user_photo == "") {
                                $user_photo = 'user_img.jpg';
                            }
                            ?>

                            <li class="ProductItem"> 
                                <a class="fancybox" data-fancybox-group="user profile" href="upload/user_photo/<?php echo $user_photo; ?>" title="<?php echo $user_name; ?>">
                                    <img class="proImg " src="upload/user_photo/<?php echo $user_photo; ?>" alt="User Image" >
                                </a>
                                <div class="productDesc">
                                    <?php if ($paid_user_status) { ?><div class="large-2" style="float: left;"><?php echo user_verification($paid_user_status); ?></div><?php } ?>
                                    <div class="productDescLft">
                                        <div class="" >
                                            <div data-tooltip="" data-options="disable_for_touch:true" class="large-10 has-tip tip-top radius heading-fix" title="View <?php echo $user_name; ?> Profile"  style="text-overflow:ellipsis;">
                                                <a href="<?php echo base_url() ?>user/<?php echo $user_id; ?>"><?php echo $user_name; ?></a>
                                            </div>
                                        </div>
                                        <span class="font12 colorGray height-fix"><?php echo $user_about; ?></span>
                                        <!--<div class="rating8"> </div>-->
                                    </div>
                                    <div class="productDescRght clearfix">
                                        <?php echo $this->load->home_m->get_user_rating($user_id); ?> 
                                    </div>
                                    <!---->

                                </div>
                                <div class="clearBoth"></div>
                            </li>



                            <?php
                        }
                    }
                    ?>
                    <!--<div class="clearBoth"></div>-->
                </ul>    
            </div>

            <!-- Best Sellers End --> 


        </div>
        <div class="large-9 columns ">

            <!-- Best Sellers End --> 
            <div class="whiteBkg">
                <div id="bxslidertwo">
                    <ul class="bxslider">
                        <li><img src="<?php echo base_url(); ?>images/01.jpg" alt="image1"/></li>
                        <li><img src="<?php echo base_url(); ?>images/02.jpg" alt="image2" /></li>
                        <li><img src="<?php echo base_url(); ?>images/03.jpg" alt="image3" /></li>
                        <li><img src="<?php echo base_url(); ?>images/04.jpg" alt="image4" /></li> 
                        <li><img src="<?php echo base_url(); ?>images/05.jpg" alt="image5" /></li>
                    </ul>
                </div>
                <!--Top Slider comments ended here by @Shamx-->
                <!-- Footer Four links start --> 
                <div class="mainBxes_1">
                    <div class="PostInventoryAll">
                        <div class="titlHed">
                            <h4 class="hed_4">Our Services</h4>
                        </div>
                        <div class="large-3 columns">
                            <div class="box">
                                <p><a href="#"><img src="img/Layer-531.png" alt="Layer"></a></p>
                                <div class="">
                                    <span class="font16">Post Inventory</span>
                                    <section class=" "> <h6 style="display: none;">Post Inventory</h6> Afrebay breaks down cultural and payment barriers that typically come with ecommerce transactions between America and emerging markets in Africa. As an Afrebay seller, you can conduct your trade transactions <section style="display:none;" id="pstInvt"><h6 style="display: none;">Post Inventory</h6>directly from the source. Reach new markets, increase your global market visibility, and make viable business connections, while Afrebay assumes the risk of offshore transactions.</section> <span id="pstInvbtn">Read more... </span>
                                    </section>
                                    <?php
                                    $user_id = $this->session->userdata('user_id');
                                    if ($user_id) {
                                        ?>
                                        <a href="manage_inventory/product" class="orngBtn" id="seller_home_post">Post  Product <img src="img/btnArw_1.png" width="8" alt="Post Product"></a>
                                    <?php } else { ?>
                                        <a href="sign_up" class="orngBtn" id="seller_home_post">Register As Seller  <img src="img/btnArw_1.png" width="8" alt="Register As Seller"></a>
                                    <?php } ?>
                                    <div class="clearBoth"></div>
                                </div>
                            </div>
                        </div>
                        <div class="large-3 columns">
                            <div class="box">
                                <p><a href="#"> <img src="img/Layer-681.png" alt="Layer"></a></p>
                                <div class="">
                                    <span class="font16"> Request Products </span>
                                    <section class=" "><h6 style="display: none;">Request Products</h6>Afrebay buyers can seamlessly purchase products from American businesses or African merchants, such as apparel, chemicals and agricultural commodities. Buyers also have access to a variety of services, <section id="reqProd" style="display:none;"><h6 style="display: none;">Request Products</h6>including architects, accountants, doctors, insurance, and more.</section><span id="reqProdbtn">Read more...</span> 
                                    </section>
                                    <?php
                                    $user_id = $this->session->userdata('user_id');
                                    if ($user_id) {
                                        ?>
                                        <a href="manage_requests/post_buy_request" class="orngBtn" id="buyer_home_post">Post Request <img src="img/btnArw_1.png" width="8" alt="Post Request"></a>
                                    <?php } else { ?>
                                        <a href="sign_up" class="orngBtn" id="buyer_home_post">Register As Buyer  <img src="img/btnArw_1.png" width="8" alt="Register As Buyer"></a>
                                    <?php } ?>
                                    <div class="clearBoth"></div>
                                </div>
                            </div>
                        </div>
                        <div class="large-3 columns">
                            <div class="box">
                                <p><a href="#"><img src="img/Layer-30-copy-4.png" alt="Layer"></a></p>
                                <div class="">
                                    <span class="font16">Business Ambassador</span>
                                    <section class=" "><h6 style="display: none;">Business Ambassador</h6>Afrebay Business Ambassadors reap many rewards, including earning revenue and making new connections locally and internationally. You will have opportunity to enroll <section style="display:none;" id="busAmb"><h6 style="display: none;">Business Ambassador</h6>manufacturers, merchants and farmers in your locality to access global marketplace offer by Afrebay.com. As a Business Ambassador, you will become involved inside and outside of your community and receive invitations to Afrebay’s exclusive meetings and events that are inside and outside of your country of origin. </section><span id="busAmbbtn">Read more...</span>
                                    </section>
                                    <?php
                                    $user_id = $this->session->userdata('user_id');
                                    if ($user_id) {
                                        ?>
                                        <a href="user_login/ambassoder" class="orngBtn" id="started">Get Started  <img src="img/btnArw_1.png" width="8" alt="Get Started"></a>
                                    <?php } else { ?>
                                        <a href="sign_up" class="orngBtn" id="started">Get Started  <img src="img/btnArw_1.png" width="8" alt="Get Started"></a>
                                    <?php } ?>

                                    <div class="clearBoth"></div>
                                </div>
                            </div>
                        </div>
                        <div class="large-3 columns">
                            <div class="box">
                                <p><a href="#"><img src="img/Layer-w681.png" alt="Layer"></a></p>
                                <div class="">
                                    <span class="font16">Business Alliance</span>
                                    <section class=" "><h6 style="display: none;">Business Alliance</h6>Afrebay Business Alliance provides an avenue for businesses seeking technical consultations or advisors, foreign investors, or strategic partners to consummate a deal. Afrebay helps seek out companies in need, <section id="busAli" style="display:none;"><h6 style="display: none;">Business Alliance</h6>facilitates any meetings, and navigates the foreign requirements that accompany a deal. </section><span id="busAlibtn">Read more...</span>
                                    </section>
                                    <a href="sign_up" class="orngBtn" id="started2">Get Started  <img src="img/btnArw_1.png" width="8" alt="Get Started"></a>
                                    <div class="clearBoth"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="clearBoth"></div>
                </div>
                <!-- Footer Four links End -->
                <div class="mainBxes_1">
                    <?php if (!empty($BestSellingProducts)) {
                        ?>
                        <!-- Weekly Bestselling Start-->
                        <div class="titlHed wekHed">
                            <h4 class="hed_4">Weekly Bestselling</h4>
                        </div>
                        <div class="">
                            <div class="slider1">
                                <?php
                                if (!empty($BestSellingProducts)) {
                                    for ($bs = 0; $bs < count($BestSellingProducts); $bs++) {
                                        $no = $bs + 1;
                                        $product_id = $BestSellingProducts[$bs]->p_id;
                                        $product_title = $BestSellingProducts[$bs]->p_title;
                                        $product_desc = $BestSellingProducts[$bs]->p_desc;
                                        $product_rate = $BestSellingProducts[$bs]->p_ppu;
                                        $product_uom = $BestSellingProducts[$bs]->uom_name;
                                        $product_price = $BestSellingProducts[$bs]->p_ppu;
                                        $product_gallery = $BestSellingProducts[$bs]->p_gallery;
                                        $product_total_order = $BestSellingProducts[$bs]->total_sale;
                                        $product_img = explode(',', $product_gallery);
                                        $p_image = "";
                                        if (isset($product_img[2]) && $product_img[2] != '') {
                                            $p_image = $product_img[2];
                                        }
                                        if (isset($product_img[1]) && $product_img[1] != '') {
                                            $p_image = $product_img[1];
                                        }
                                        if ($product_img[0] != '') {
                                            $p_image = $product_img[0];
                                        }
                                        ?>
                                        <div class="slide">
                                            <p class="font14 slideInfo">
                                                <span class="slideNum"><?php echo $no; ?></span> 
                                                <span class="ordCrcl_1"> Orders <b> <?php echo $product_total_order; ?> </b></span>
                                            </p>
                                            <p><a href="<?php echo base_url() . 'product/' . $product_id; ?>"><img src="<?php echo base_url(); ?>upload/product_photos/<?php echo $p_image; ?>" alt="<?php echo $product_title; ?>"></a></p>
                                            <div class="">
                                                <div  data-tooltip="" data-options="disable_for_touch:true" class="has-tip tip-top radius heading-fix" title="<?php echo $product_title; ?>"  style="text-overflow:ellipsis;">
                                                    <a href="<?php echo base_url() . 'product/' . $product_id; ?>"><?php echo $product_title; ?></a>
                                                </div>
                                                <span class="newPrice">$<?php echo $product_price; ?></span>
                                                <?php echo $this->load->home_m->product_rating($product_id); ?>
                                                <div class="clearBoth"></div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <div class="clearBoth"></div>

                        <!-- Weekly Bestselling End-->
                    <?php } ?>
                </div>
                <!-- Hot & New Start --> 
                <div class="mainBxes_1">
                    <div class="titlHed">
                        <h4 class="hed_4">Hot & New</h4>
                    </div>
                    <!-- <h1 class="mainHeading">Hot & New</h1>-->
                    <?php
                    if (!empty($AllProducts)) {
                        $cnt = 0;
                        for ($ap = 0; $ap < 8; $ap++) {
                            if ($ap < count($AllProducts)) {
                                $product_id = $AllProducts[$ap]->p_id;
                                $product_title = $AllProducts[$ap]->p_title;
                                $product_desc = $AllProducts[$ap]->p_desc;
                                $product_rate = $AllProducts[$ap]->p_ppu;
                                $product_type = $AllProducts[$ap]->p_sell_type;
                                $product_uom = $AllProducts[$ap]->uom_name;
                                $product_gallery = $AllProducts[$ap]->p_gallery;
                                $product_img = explode(',', $product_gallery);
                                $p_image = "";
                                if (isset($product_img[2]) && $product_img[2] != '') {
                                    $p_image = $product_img[2];
                                }
                                if (isset($product_img[1]) && $product_img[1] != '') {
                                    $p_image = $product_img[1];
                                }
                                if ($product_img[0] != '') {
                                    $p_image = $product_img[0];
                                }

                                $cnt++;
                                if ($cnt == 1) {
                                    ?>
                                    <div class="WeeklyBestSelling">
                                    <?php }
                                    ?>
                                    <div class="slide large-3 columns">
                                        <p>
                                            <a href="<?php echo base_url() . 'product/' . $product_id; ?>">
                                                <img src="<?php echo base_url() . 'upload/product_photos/' . $p_image; ?>" style="height:142px;" alt="<?php echo $product_title; ?>">
                                            </a>
                                        </p>
                                        <div class="">
                                            <div  data-tooltip="" data-options="disable_for_touch:true" class="has-tip tip-top radius heading-fix" title="<?php echo $product_title; ?>"  style="text-overflow:ellipsis;">
                                                <a href="<?php echo base_url() . 'product/' . $product_id; ?>"><?php echo $product_title; ?></a>
                                            </div>
                                            <section class="font11 colorGray height-fix"><h6 style="display: none;">Product Description</h6><?php echo $product_desc; ?></section>
                                            <section class="ProPrice"><h6 style="display: none;">Product Rate</h6> <strong class="font14 heading-fix left" style="text-overflow:ellipsis;">$<?php echo $product_rate; ?>/<?php echo $product_uom; ?></strong> 
                                                <?php if ($product_type == "Fixed price") { ?>
                                                    <a href="javascript:onclick:void(0);" onclick="return addCart('<?php echo $product_id; ?>', 1);" class="showmsg"><img src="img/icons/add-to-cart-dark.png" alt="Add to cart"></a>
                                                <?php } ?>
                                                <?php if ($product_type == "Auction") { ?>
                                                    <a href="<?php echo base_url() . 'product/' . $product_id; ?>"><img  style="width:30;height:23px;" src="<?php echo base_url(); ?>img/icons/auction.png"/></a>
                                                <?php } ?>
                                                <div class="clearBoth"></div>
                                            </section>
                                            <div class="clearBoth"></div>
                                        </div>
                                    </div>
                                    <?php if ($cnt == 4) { ?>
                                    </div>
                                    <div class="clearBoth"></div>
                                    <?php
                                }
                                if ($cnt % 4 == 0) {
                                    $cnt = 0;
                                }
                                ?>
                                <?php
                            } else {
                                echo '<div class="clearBoth"></div>';
                            }
                        }
                    }
                    ?>
                    <!-- Hot & New End --> 
                    <div class="clearBoth"></div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Main Contnr End --> 
<!--home end-->
