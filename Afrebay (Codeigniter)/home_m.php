<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Home_m extends CI_Model {

    function insert($table, $data) {
        $this->db->insert($table, $data);
        $num = $this->db->insert_id();
        if ($num) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function getWhere($table, $where) {
        $this->db->where($where);
        $getdata = $this->db->get($table);

        $num = $getdata->num_rows();
        if ($num > 0) {
            $arr = $getdata->result();
            foreach ($arr as $rows) {
                $data[] = $rows;
            }
            $getdata->free_result();
            return $data;
        } else {
            return false;
        }
    }

    /* update auction start */

    function update_auction() {
        $data = $this->getCustom('SELECT * FROM `tbl_products` where NOW() > DATE_ADD(p_datetime, INTERVAL p_auc_duration DAY) and p_st_price !=0 and p_status != 6');



        if (count(array_filter((array) $data)) > 0) {
            for ($i = 0; $i < count($data); $i++) {
                /* get max bid for product start */
                $bid = $this->getCustom('SELECT max(bid_id) as bid_id FROM `tbl_bid` where bid_product = ' . $data[$i]->p_id);
                /* get max bid for product end */

                /*
                  0 for candidates
                  1 for winner
                 */

                if ($bid && $bid[0]->bid_id != '') {
                    /* update win user start */
                    $arr_bid = array('bid_status' => 1);
                    $where_bid = array('bid_id' => $bid[0]->bid_id);
                    $update = $this->update('tbl_bid', $where_bid, $arr_bid);
                    /* update win user end */

                    /* get all winners start */
                    $where_bid = 'bid_id = ' . $bid[0]->bid_id;
                    $winner_user = $this->getWhere('tbl_bid', $where_bid);
                    /* get all winners end */

                    /**/
                    $this->inConv($winner_user[0]->bid_user, $data[$i]->p_user, $data[$i]->p_id);
                    /**/
                }



                /* update product start */
                $arr_product = array('p_status' => 6);
                $where = array('p_id' => $data[$i]->p_id);
                $update = $this->update('tbl_products', $where, $arr_product);
                /* update product end */
            }
        }

        $user_id = $this->session->userdata('user_id');

        if ($user_id) {

            /* check  winning user start */
            $where_user = 'bid_user = ' . $user_id;
            $bidData = $this->getWhere('tbl_bid', $where_user);
            /* check winning user end */


            /* insert product in cart start */
            if ($bidData) {
                for ($i = 0; $i < count($bidData); $i++) {
                    /* get order data start */
                    $where_order = 'or_product = ' . $bidData[$i]->bid_product . ' and or_user = ' . $bidData[$i]->bid_user . ' and or_status != 0';
                    $orderData = $this->getWhere('tbl_orders', $where_order);
                    /* get order data end */

                    if (empty($orderData) == true) {
                        $this->inProCart($bidData[$i]->bid_product);
                    }
                }
            }
            /* insert product in cart end */
        }
    }

    /* update auction end */

    /* insert conversation start */

    function inConv($user_id, $con_user_id, $product_id) {
        $con_date = date("Y-m-d  H:i:s");

        /* get conversation id  start */
        $where_conversation = '(con_sender =' . $user_id . ' and con_reciever =' . $con_user_id . ') or (con_sender =' . $con_user_id . ' and con_reciever =' . $user_id . ')';
        $config['conversationId'] = $this->home_m->getWhere('tbl_conversation', $where_conversation);
        /* get conversation id  end */

        if (empty($config['conversationId']) == true) {
            /* insert first conversation start */
            $arr_conv = array(
                'con_sender' => $con_user_id,
                'con_reciever' => $user_id,
                'con_datetime' => $con_date
            );
            $this->home_m->insert('tbl_conversation', $arr_conv);
            /* insert first conversation end */

            /* get conversation id  start */
            $where_conversation = '(con_sender =' . $user_id . ' and con_reciever =' . $con_user_id . ') or (con_sender =' . $con_user_id . ' and con_reciever =' . $user_id . ')';
            $config['conversationId'] = $this->home_m->getWhere('tbl_conversation', $where_conversation);
            /* get conversation id  end */
        }


        /* insert  conversation reply start */

        $con_msg = 'You won my product';

        $arr_conv_reply = array(
            'cr_msg' => $con_msg,
            'cr_sender' => $con_user_id,
            'cr_auction' => $product_id,
            'c_id' => $config['conversationId'][0]->con_id,
            'cr_datetime' => $con_date
        );

        $this->home_m->insert('tbl_conversation_reply', $arr_conv_reply);

        /* insert  conversation reply end */
    }

    /* insert conversation end */




    /* insert product in cart start */

    function inProCart($p_id) {
        $where = 'tbl_products.p_id = ' . $p_id;
        $ProductDetail = $this->home_m->getProducts($where);

        $product_dtl = array(
            'id' => $ProductDetail[0]->p_id,
            'qty' => 1,
            'price' => $ProductDetail[0]->p_ppu,
            'uom' => $ProductDetail[0]->uom_name,
            'seller_id' => $ProductDetail[0]->p_user,
            'qty_aval' => $ProductDetail[0]->p_qty_aval,
            'gallery' => $ProductDetail[0]->p_gallery,
            'name' => 'item_' . $ProductDetail[0]->p_id,
            'title' => $ProductDetail[0]->p_title,
            'detail' => $ProductDetail[0]->p_desc,
            'ship_chrg' => 0
        );
        if (count($this->cart->contents()) < 100) {
            if ($ProductDetail[0]->p_user != $this->session->userdata('user_id')) {
                $is_add = $this->cart->insert($product_dtl);
            }
        } else {
            /* Notification start */
            $this->session->set_userdata('header_msg_txt', 'You have added maximum items to cart.No more  items can be add now.');
            $this->session->set_userdata('header_msg_type', '1');
            /* Notification end */
            //echo 'success';
        }
    }

    /* insert product in cart end */

    /* get proposal data start */

    function getProposal($where = null) {
        $str_query = '';
        if (!isset($where)) {
            $str_query = 'SELECT * from tbl_proposal';
        } else {
            $str_query = 'SELECT tbl_orders.*,tbl_proposal.*,tbl_users.user_name,(tbl_proposal.pr_price) as order_total FROM tbl_orders inner join tbl_proposal on tbl_proposal.pr_id=tbl_orders.or_pr_id inner join tbl_users on tbl_users.user_id=tbl_proposal.user_id   where ' . $where . '';
        }
        $order_data = $this->getCustom($str_query);
        return $order_data;
    }

    /* get proposal data end */



    /* get shopping order product start */

    public function shopping_order($where) {
        $data = $this->getCustom('SELECT * FROM `tbl_orders` inner join tbl_products on tbl_orders.or_product inner join tbl_users on tbl_products.p_user=tbl_users.user_id inner join tbl_uom on tbl_products.p_uom=tbl_uom.uom_id where ' . $where);
        return $data;
    }

    /* get shopping order product end */


    /* get child  order product start */

    public function childProdOrder($where) {
        $data = $this->getCustom('SELECT count(or_id) as total_order,SUM(or_price*or_qty) as total_amount FROM `tbl_users` inner join tbl_products on tbl_users.user_id=tbl_products.p_user inner join tbl_orders on tbl_products.p_id=tbl_orders.or_product where ' . $where);
        return $data;
    }

    /* get child  order product end */

    /* get child  proposal product start */

    public function childPropOrder($where) {
        $data = $this->getCustom('SELECT count(or_id) as total_order,SUM(or_price*or_qty) as total_amount FROM `tbl_users` inner join tbl_proposal on tbl_users.user_id=tbl_proposal.user_id inner join tbl_orders on tbl_proposal.pr_id=tbl_orders.or_pr_id where ' . $where);
        return $data;
    }

    /* get child  proposal product end */



    /* get page required data start */

    public function page_req_data() {
        $user_id = $this->session->userdata('user_id');

        /* unit of measurement data start */
        $where_uom = "uom_status = 0 ORDER BY uom_name";
        $config['uomData'] = $this->getWhere('tbl_uom', $where_uom);
        /* unit of measurement data end */



        /* list of ports data start  By @SHAMX*/
        $where_port = "status = 1 ORDER BY port_name";
        $config['portData'] = $this->getWhere('btl_port', $where_port);
        /* Ports data end */

        /* All Category data start */
        $where_cat = 'parent_id = 0 and (populer_status = 1 or populer_status = 2) ORDER BY cat_name';
        $config['getAllMainCateg'] = $this->getWhere('tbl_categories', $where_cat);
        /* All Category data end */

        /* All Sub-category data start */
        $where_sub_cat = 'parent_id !=0 and (populer_status = 1 or populer_status = 2)';
        $config['getAllSubCateg'] = $this->getWhere('tbl_categories', $where_sub_cat);
        /* All Sub-category data end */

        /* Payment Terms data start */
        $where_pay_t = "pa_te_status = 0 ORDER BY pa_te_name";
        $config['PaymentTerms'] = $this->getWhere('tbl_pay_term', $where_pay_t);
        /* Payment Terms data end */

        /* Price Terms data start */
        $where_pr_t = "pr_te_status = 0 ORDER BY pr_te_name";
        $config['PriceTerms'] = $this->getWhere('tbl_pri_term', $where_pr_t);
        /* Price Terms data end */


        /* configuration data start */
        $where_config = "confi_id = 1";
        $config['configData'] = $this->getWhere('tbl_configuration', $where_config);
        /* configuration data end */

        $where_partners = 'status = 1';
        $config['getAllPartners'] = $this->getWhere('tbl_partners', $where_partners);


        /* update auction product start */
        $this->update_auction();
        /* update auction product end */


        if ($user_id != '') {
            /* user data start */
            $where_user = array('user_id' => $user_id);
            $config['userData'] = $this->getWhere('tbl_users', $where_user);
            /* user data end */

            /* get conversation header data start */
            $where_conversation = $user_id;
            $config['convHeaderData'] = $this->home_m->get_ConHeaderData($where_conversation);
            //$config['allCconvHeaderData'] = $this->home_m->get_AllConHeaderData($where_conversation);
            /* get conversation header data end */
        }
        return $config;
    }

    /* get page required data end */


    /* get order data start */

    function getOrder($where = null) {
        $str_query = '';
        if (!isset($where)) {

            $str_query = 'SELECT tbl_orders.*,tbl_users.user_name, tbl_products.p_title as productName  FROM `tbl_orders` inner join tbl_products on tbl_products.p_id=tbl_orders.or_product inner join tbl_users on tbl_users.user_id=tbl_products.p_user ';
        } else {

            $str_query = 'SELECT tbl_orders.*,sum(tbl_orders.or_price*tbl_orders.or_qty) as order_total,tbl_users.user_name, tbl_products.p_title as productName FROM `tbl_orders` inner join tbl_products on tbl_products.p_id=tbl_orders.or_product inner join tbl_users on tbl_users.user_id=tbl_products.p_user where ' . $where . '';
        }

        $order_data = $this->getCustom($str_query);
        return $order_data;
    }

    /* get order data end */


    /* get Best Sellers start */

    function getBestSellers() {
        $str_query = 'select * from (select tbl_users.*,count(tbl_orders.or_id) as total_orders from tbl_orders inner join tbl_products on tbl_products.p_id=tbl_orders.or_product inner join tbl_users on tbl_users.user_id=tbl_products.p_user where tbl_products.p_status!=6 and tbl_products.p_status!=5 group by tbl_products.p_user) as temp_table order by total_orders desc limit 0,6';
        $result = $this->getCustom($str_query);
        return $result;
    }

    /* get Best Sellers end */

    /* get bid product for buyer start */

    function get_bb_product($where) {
        $data = $this->getCustom('SELECT tbl_bid.*,tbl_products.*,tbl_uom.*,count(bid_id) as total_bid FROM `tbl_bid` inner join tbl_products on tbl_bid.bid_product= tbl_products.p_id inner join tbl_uom on tbl_products.p_uom=tbl_uom.uom_id  where ' . $where);
        return $data;
    }

    /* get bid product for buyer end */

    /* get Best Selling Product start */

    function getBestSellingProducts() {
        $str_query = 'Select * from (SELECT count(tbl_orders.or_product) as total_sale,tbl_products.*,tbl_categories.cat_name,tbl_uom.uom_name,(select cat_name from tbl_categories where cat_id=tbl_products.p_scat ) as scat_name,tbl_users.user_name FROM `tbl_orders` inner join tbl_products on tbl_products.p_id=tbl_orders.or_product inner join tbl_categories on tbl_categories.cat_id=tbl_products.p_cat inner join tbl_users on tbl_users.user_id=tbl_products.p_user inner join tbl_uom on tbl_uom.uom_id=tbl_products.p_uom where tbl_products.p_status!=6 and tbl_products.p_status!=5 group by or_product) as temp_table order by total_sale Desc limit 0,8';
        $result = $this->getCustom($str_query);
        return $result;
    }

    /* get Best Selling Product end */

    /* get all proposal data start */

    function getAllProposals($where) {
        $data = $this->getCustom('SELECT * FROM (`tbl_proposal`) JOIN `tbl_pri_term` ON `tbl_proposal`.`pro_pri_term`=`tbl_pri_term`.`pr_te_id` JOIN `tbl_product_request` ON `tbl_proposal`.`br_id`=`tbl_product_request`.`br_id` JOIN `tbl_users` ON `tbl_proposal`.`user_id`=`tbl_users`.`user_id` JOIN `tbl_uom` ON `tbl_proposal`.`pr_uom`=`tbl_uom`.`uom_id` WHERE ' . $where . ' ORDER BY `pr_id` desc ');
        return $data;
    }

    /* get all proposal data end */

    /* get bid data start */

    function getBidDetail($where) {
        $data = $this->getCustom('SELECT * FROM `tbl_bid` left outer join tbl_products on tbl_bid.bid_product=tbl_products.p_id left outer join tbl_users on tbl_bid.bid_user=tbl_users.user_id where ' . $where . ' ORDER BY bid_id DESC');
        return $data;
    }

    /* get bid data end */

    /* get search product start */

    function getSrcProduct($where) {
        $data = $this->getCustom('SELECT * FROM `tbl_products` inner join tbl_uom on tbl_products.p_uom=tbl_uom.uom_id where ' . $where);
        return $data;
    }

    /* get search product start */


    /* get where with order start */

    function getWhereWithO($table, $where, $order_field) {
        $this->db->order_by($order_field, "desc");
        $this->db->where($where);
        $getdata = $this->db->get($table);

        $num = $getdata->num_rows();
        if ($num > 0) {
            $arr = $getdata->result();
            foreach ($arr as $rows) {
                $data[] = $rows;
            }
            $getdata->free_result();
            return $data;
        } else {
            return false;
        }
    }

    /* get where with order end */

    /* get cash out order start */

    function get_or_cash_out($user_id) {
        $or_query_s = 'SELECT tbl_orders.* FROM `tbl_orders` inner join tbl_products on tbl_orders.or_product=tbl_products.p_id where tbl_orders.or_status=3 and tbl_products.p_user= ' . $user_id . ' order by tbl_orders.or_id desc';
        $or_data_s = $this->getCustom($or_query_s);

        $or_query_p = 'SELECT tbl_orders.* FROM `tbl_orders` inner join tbl_proposal on tbl_orders.or_pr_id=tbl_proposal.pr_id where tbl_orders.or_status=3 and tbl_proposal.user_id = ' . $user_id . ' order by tbl_orders.or_id desc';
        $or_data_p = $this->getCustom($or_query_p);

        return array_filter(array_merge((array) $or_data_s, (array) $or_data_p));
    }

    /* get cash out order start */

    function getAll($table) {
        $data = $this->db->get($table);
        $get = $data->result();

        $num = $data->num_rows();

        if ($num) {
            return $get;
        } else {
            return false;
        }
    }

    function update($table, $where, $data) {
        $this->db->where($where);
        $update = $this->db->update($table, $data);

        if ($update) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function delete($table, $where) {
        $this->db->where($where);
        $this->db->limit('1');
        $del = $this->db->delete($table);
        if ($del) {
            return true;
        } else {
            return false;
        }
    }

    /* specific category function start */

    function getSpecificCat($tablename, $cond1) {
        $this->db->where($cond1);
        $where = '(tbl_categories.populer_status=1 or tbl_categories.populer_status=2)';
        $this->db->where($where);
        $getdata = $this->db->get($tablename);

        $num = $getdata->num_rows();
        if ($num > 0) {
            $arr = $getdata->result();
            foreach ($arr as $rows) {
                $data[] = $rows;
            }
            $getdata->free_result();
            return $data;
        } else {
            return false;
        }
    }

    /* specific category function end */

    /* get all request start */

    function get_all_request($where) {
        $str_query = 'SELECT tbl_product_request.*,count(tbl_proposal.pr_id) as total_proposal FROM `tbl_product_request` left outer join tbl_proposal on tbl_product_request.br_id=tbl_proposal.br_id where tbl_product_request.user_id = ' . $where . ' group by tbl_product_request.br_id order by tbl_product_request.br_id desc';
        $requests = $this->getCustom($str_query);
        return $requests;
    }

    /* get all request end */


    /* get all product start */

    function getAllProducts() {
     $str_query = 'SELECT tbl_products.*,tbl_categories.cat_name,tbl_uom.uom_name from tbl_products inner join tbl_categories on tbl_categories.cat_id=tbl_products.p_cat inner join tbl_uom on tbl_uom.uom_id=tbl_products.p_uom where tbl_products.p_user=' . $this->session->userdata('user_id') . ' and (tbl_products.p_status =1 or tbl_products.p_status =2) order by tbl_products.p_id DESC';
     $requests = $this->getCustom($str_query);
     return $requests;
    }

    /* get all product end */

    /**
     * Get all trash products
     * @Shamx
     * @return array|bool
     */
    function getAllTrashProducts() {
        $str_query = 'SELECT tbl_products.*,tbl_categories.cat_name,tbl_uom.uom_name from tbl_products inner join tbl_categories on tbl_categories.cat_id=tbl_products.p_cat inner join tbl_uom on tbl_uom.uom_id=tbl_products.p_uom where tbl_products.p_user=' . $this->session->userdata('user_id') . ' and tbl_products.p_status =5  order by tbl_products.p_id DESC';
        $requests = $this->getCustom($str_query);
        return $requests;
    }

    /***Trash products listing ended here***/
    /* get user product start */

    function getUserProducts($where) {
        $str_query = 'SELECT tbl_products.*,tbl_categories.cat_name,tbl_uom.uom_name from tbl_products inner join tbl_categories on tbl_categories.cat_id=tbl_products.p_cat inner join tbl_uom on tbl_uom.uom_id=tbl_products.p_uom where ' . $where;
        $requests = $this->getCustom($str_query);
        return $requests;
    }

    /* get user product end */




    /* get active product start */

    function getActiveProducts() {
        $str_query = 'SELECT (count(p_id)) as total from tbl_products where p_user=' . $this->session->userdata('user_id') . ' and p_status=1';
        $requests = $this->getCustom($str_query);
        return $requests;
    }

    /* get active product end */

    /* get Suspended product start */

    function getSuspendedProducts() {
        $str_query = 'SELECT (count(p_id)) as total from tbl_products where p_user=' . $this->session->userdata('user_id') . ' and  p_status=2';
        $requests = $this->getCustom($str_query);
        return $requests;
    }

    /* get Suspended product end */

    /* get all proposal request start */

    function get_proposal_request($where) {
        $str_query = 'SELECT tbl_product_request.*,tbl_users.*,count(tbl_orders.or_id) as total_order FROM `tbl_product_request` inner join tbl_proposal on tbl_product_request.br_id=tbl_proposal.br_id  inner join tbl_users on tbl_product_request.user_id=tbl_users.user_id  left outer join tbl_orders on tbl_proposal.pr_id=tbl_orders.or_pr_id where tbl_proposal.user_id = ' . $where . ' group by tbl_product_request.br_id  order by tbl_product_request.br_id desc';
        $requests = $this->getCustom($str_query);
        return $requests;
    }

    /* get all proposal request end */

    /* get order data start */

    function getOrderData($where) {
        $data = $this->getCustom('SELECT * FROM `tbl_orders` inner join tbl_products on tbl_orders.or_product=tbl_products.p_id  inner join tbl_users on tbl_orders.or_user=tbl_users.user_id inner join tbl_uom on tbl_products.p_uom=tbl_uom.uom_id  where tbl_orders.or_no = ' . $where);
        return $data;
    }

    /* get order data end */

    /* get proposal order data start */

    function proOrData($where) {
        $data = $this->getCustom('SELECT * FROM `tbl_orders` inner join tbl_proposal on tbl_orders.or_pr_id=tbl_proposal.pr_id  inner join tbl_users on tbl_orders.or_user=tbl_users.user_id inner join tbl_uom on tbl_proposal.pr_uom=tbl_uom.uom_id  where tbl_orders.or_no = ' . $where);
        return $data;
    }

    /* get proposal order data end */

    /* get order seller data start */

    function orderSellerData($where) {
        $data = $this->getCustom('SELECT * FROM `tbl_orders` inner join tbl_products on tbl_orders.or_product=tbl_products.p_id inner join tbl_users on tbl_products.p_user=tbl_users.user_id  where tbl_orders.or_no = ' . $where);
        return $data;
    }

    /* get order seller data end */

    /* get order proposal seller data start */

    function orProSellerData($where) {
        $data = $this->getCustom('SELECT * FROM `tbl_orders` inner join tbl_proposal on tbl_orders.or_pr_id = tbl_proposal.pr_id inner join tbl_users on tbl_proposal.user_id=tbl_users.user_id  where tbl_orders.or_no = ' . $where);
        return $data;
    }

    /* get order proposal seller data end */

    /* get order product for Buyer and Seller start */

    function getCustomOrdersS($where, $limit) {
        $data = $this->getCustom('SELECT tbl_orders.*,tbl_users.user_name,tbl_proposal.*,tbl_uom.uom_name FROM `tbl_orders` inner join tbl_proposal on tbl_proposal.pr_id=tbl_orders.or_pr_id inner join tbl_product_request on tbl_product_request.br_id=tbl_proposal.br_id inner join tbl_users on tbl_users.user_id=tbl_product_request.user_id inner join tbl_uom on tbl_uom.uom_id=tbl_proposal.pr_uom  where ' . $where . ' ' . $limit . '');
        return $data;
    }

    /* get order product for Buyer and Seller end */

    /* get order product for Buyer and Seller start */

    function getOrders($where, $limit) {

        $data = $this->getCustom('SELECT * from (SELECT tbl_orders.*,sum(tbl_orders.or_price*tbl_orders.or_qty) as order_total,tbl_users.user_name,tbl_products.* FROM `tbl_orders` inner join tbl_products on tbl_products.p_id=tbl_orders.or_product inner join tbl_users on tbl_users.user_id=tbl_products.p_user where ' . $where . ' group by tbl_orders.or_no) as temp_table order by or_trans_date DESC ' . $limit . '');
        return $data;
    }

    /* get order product for Buyer and Seller end */

    /* get order product for Buyer and Seller start */

    function getOrdersS($where, $limit) {
        $data = $this->getCustom('SELECT * from (SELECT tbl_orders.*,sum(tbl_orders.or_price*tbl_orders.or_qty) as order_total,tbl_users.user_name,tbl_products.* FROM `tbl_orders` inner join tbl_products on tbl_products.p_id=tbl_orders.or_product inner join tbl_users on tbl_users.user_id=tbl_orders.or_user where ' . $where . ' group by tbl_orders.or_no) as temp order by or_trans_date DESC ' . $limit . '');
        return $data;
    }

    /* get order product for Buyer and Seller end */

    /* get order product for Buyer and Seller start */

    function getOrderProducts($where) {
        $data = $this->getCustom('Select tbl_products.*,tbl_categories.cat_name,tbl_uom.uom_name,(select cat_name from tbl_categories where cat_id=tbl_products.p_scat ) as scat_name,tbl_users.user_name,tbl_orders.* from tbl_orders inner join tbl_products on tbl_products.p_id=tbl_orders.or_product inner join tbl_categories on tbl_categories.cat_id=tbl_products.p_cat inner join tbl_users on tbl_users.user_id=tbl_products.p_user inner join tbl_uom on tbl_uom.uom_id=tbl_products.p_uom where ' . $where . ' order by tbl_orders.or_trans_date DESC');
        return $data;
    }

    /* get order product for Buyer and Seller end */

    /* get no of bid  and maximum bid start */

    function getBidCount($where) {
        $str_query = 'SELECT count(bid_id) as total_bid,max(bid_amount) as bid_amount,count(distinct(bid_user)) as total_user FROM `tbl_bid` where ' . $where;
        $data = $this->getCustom($str_query);
        return $data;
    }

    /* get no of bid  and maximum bid end */


    /* get product review start */

    function get_product_review($product_id) {
        $str_query = 'SELECT round(avg(rev_rate),1) as rev_rate FROM `tbl_reviews` inner join tbl_orders on tbl_reviews.rev_or_no=tbl_orders.or_no where tbl_orders.or_product = ' . $product_id;
        $review = $this->getCustom($str_query);
        return $review;
    }

    /* get product review end */

    /* get portfolio start */

    function get_portfolio($user_id) {
        $arr_UId = array('id' => $user_id);
        $userData = $this->home_m->getWhere('tbl_users', $arr_UId);
        $port_folio_img = '';
        $port_folio_caption = '';

        if ($userData) {
            $port_folio_img = explode('____', $userData[0]->user_port_img);
            $port_folio_caption = explode('____', $userData[0]->user_port_ti);
        }
        $port_string = '';

        if ($port_folio_img) {
            for ($i = 0; $i < count($port_folio_img); $i++) {
                if ($port_string == '') {
                    $port_string = '{
						href : ' . "'" . base_url() . 'upload/user_portfolio/' . $port_folio_img[$i] . "'" . ',
						title :' . "'" . $port_folio_caption[$i] . "'" . '},';
                } else {
                    $port_string = $port_string . '{
						href : ' . "'" . base_url() . 'upload/user_portfolio/' . $port_folio_img[$i] . "'" . ',
						title :' . "'" . $port_folio_caption[$i] . "'" . '},';
                }
            }
        }
        return $port_string;
    }

    /* get portfolio end */

    /* get portfolio crousal start */

    function get_portfolio_crousal($user_id) {
        $arr_UId = array('id' => $user_id);
        $userData = $this->home_m->getWhere('tbl_users', $arr_UId);

        $port_folio_img = explode('____', $userData[0]->user_port_img);
        $port_folio_caption = explode('____', $userData[0]->user_port_ti);

        $port_string_start = ' 
	<div id="carousel-wrapper" style="margin:15px;">
		<div id="carousel">';

        $port_string_middle = '';
        for ($i = 0; $i < count($port_folio_img); $i++) {
            if ($port_string_middle == '') {
                $port_string_middle = '
					<span id="' . $i . '">
					<img src="' . base_url() . 'upload/user_portfolio/' . $port_folio_img[$i] . '" />
					</span>';
            } else {
                $port_string_middle = $port_string_middle . '<span id="' . $i . '">
					<img src="' . base_url() . 'upload/user_portfolio/' . $port_folio_img[$i] . '" />
					</span>';
            }
        }
        $port_string_end = '</div>
			</div>';
        return $port_string_start . $port_string_middle . $port_string_end;
    }

    /* get portfolio crousal end */

    /* get review from service start */

    function get_review_s($user_id) {
        $str_query = 'SELECT tbl_user_review.*,tbl_orders.*,tbl_users.*,tbl_conversation_reply.* FROM `tbl_user_review` inner join tbl_orders on tbl_user_review.or_id=tbl_orders.or_id inner join tbl_services on tbl_orders.service_id=tbl_services.id inner join tbl_conversation_reply on tbl_user_review.or_id=tbl_conversation_reply.cr_order inner join  tbl_users on tbl_orders.user_id=tbl_users.id where tbl_conversation_reply.cr_or_status = 3 and tbl_services.user_id = ' . $user_id . '  order by tbl_user_review.rev_id desc';
        $review = $this->getCustom($str_query);
        return $review;
    }

    /* get review from service end */

    /* get service review start */

    function get_product_reviews($where) {
        $str_query = 'SELECT * FROM `tbl_reviews` inner join tbl_orders on tbl_reviews.rev_or_no=tbl_orders.or_no  inner join tbl_users  on tbl_reviews.rev_user_f=tbl_users.user_id where ' . $where;
        $review = $this->getCustom($str_query);
        return $review;
    }

    /* get service review end */


    /* get order product for Buyer and Seller start */

    function getCustomOrders($where, $limit) {
        $data = $this->getCustom('SELECT tbl_orders.*,tbl_users.user_name,tbl_proposal.*,tbl_uom.uom_name FROM `tbl_orders` inner join tbl_proposal on tbl_proposal.pr_id=tbl_orders.or_pr_id inner join tbl_users on tbl_users.user_id=tbl_proposal.user_id inner join tbl_uom on tbl_uom.uom_id=tbl_proposal.pr_uom  where ' . $where . ' ' . $limit . '');
        return $data;
    }

    /* get order product for Buyer and Seller end */

    /* get configuration data start */

    function getConfiguration() {
        $data = $this->getCustom('SELECT * from tbl_configuration');
        return $data;
    }

    /* get configuration data end */

    /* get review from proposal start */

    function get_review_p($user_id) {
        $str_query = 'SELECT tbl_user_review.*,tbl_orders.*,tbl_users.*,tbl_conversation_reply.* FROM `tbl_user_review` inner join tbl_orders on tbl_user_review.or_id=tbl_orders.or_id inner join tbl_proposal on tbl_orders.pr_id=tbl_proposal.pr_id inner join tbl_conversation_reply on tbl_user_review.or_id=tbl_conversation_reply.cr_order inner join  tbl_users on tbl_orders.user_id=tbl_users.id where tbl_conversation_reply.cr_or_status=3  and tbl_proposal.user_id = ' . $user_id . ' order by tbl_user_review.rev_id desc';
        $review = $this->getCustom($str_query);
        return $review;
    }

    /* get review from proposal start */


    /* count with join and where start */

    function countJTAndW($tbl1, $tbl2, $field1, $field2, $where, $count_id) {
        $this->db->select('COUNT(' . $tbl1 . '.' . $count_id . ') as total');
        $this->db->from($tbl1);
        $this->db->join($tbl2, $tbl1 . '.' . $field1 . '=' . $tbl2 . '.' . $field2);
        $this->db->where($where);
        $getdata = $this->db->get();
        $num = $getdata->num_rows();
        if ($num > 0) {
            $arr = $getdata->result();
            foreach ($arr as $rows) {
                $data[] = $rows;
            }
            $getdata->free_result();
            return $data;
        } else {
            return false;
        }
    }

    /* count with join and where end */

    /* get all proposal by per request start */

    function countProPerReq($tbl1, $tbl2, $field1, $field2, $where, $count_id) {
        $this->db->select('COUNT(' . $tbl1 . '.' . $count_id . ') as total_pro,' . $tbl2 . '.' . $field1 . ' as rs_id');
        $this->db->from($tbl1);
        $this->db->join($tbl2, $tbl1 . '.' . $field1 . '=' . $tbl2 . '.' . $field2);
        $this->db->where($where);
        $this->db->group_by($tbl2 . '.' . $field1);
        $getdata = $this->db->get();
        $num = $getdata->num_rows();
        if ($num > 0) {
            $arr = $getdata->result();
            foreach ($arr as $rows) {
                $data[] = $rows;
            }
            $getdata->free_result();
            return $data;
        } else {
            return false;
        }
    }

    /* get all proposal by per request end */

    function getCustom($str_query) {
       
        $getdata = $this->db->query($str_query);
        $num = $getdata->num_rows();
        if ($num > 0) {
            $arr = $getdata->result();
            foreach ($arr as $rows) {
                $data[] = $rows;
            }
            $getdata->free_result();
            return $data;
        } else {
            return false;
        }

    }

    /* get all recent request start */

    function getReReq($where, $limit_from, $limit_to) {
        $recent_request = $this->getCustom('SELECT tbl_product_request.*,tbl_users.*,count(tbl_proposal.pr_id) as total_proposal FROM `tbl_product_request` left outer join tbl_proposal on tbl_product_request.br_id = tbl_proposal.br_id INNER JOIN tbl_users ON tbl_product_request.user_id = tbl_users.user_id where ' . $where . ' limit ' . $limit_from . ',' . $limit_to);
        return $recent_request;
    }

    /* get all recent request end */

    /* get buyer data start */

    function or_buyer_data($where) {
        $buyer_query = 'SELECT * FROM `tbl_orders` inner join tbl_users on tbl_orders.user_id=tbl_users.id where tbl_orders.or_no = ' . "'" . $where . "'";
        $buyer_data = $this->getCustom($buyer_query);
        return $buyer_data;
    }

    /* get buyer data end */

    /* get seller data from proposal start */

    function or_seller_data_p($where) {
        $seller_query = 'SELECT * FROM `tbl_orders` inner join tbl_proposal on tbl_orders.pr_id=tbl_proposal.pr_id  inner join tbl_users on tbl_proposal.user_id = tbl_users.id where ' . $where;
        $seller_data = $this->getCustom($seller_query);
        return $seller_data;
    }

    /* get seller data from proposal end */

    /* get seller data from service start */

    function or_seller_data_s($where) {
        $seller_query = 'SELECT tbl_orders.*,tbl_users.*  FROM `tbl_orders` inner join tbl_services on tbl_orders.service_id=tbl_services.id  inner join tbl_users on tbl_services.user_id=tbl_users.id where ' . $where;
        $seller_data = $this->getCustom($seller_query);
        return $seller_data;
    }

    /* get seller data from service end */

    /* get order item for service start */

    function or_item_s($where) {
        $item_query = 'SELECT * FROM `tbl_orders` inner join tbl_services on tbl_orders.service_id=tbl_services.id where tbl_orders.or_no =' . "'" . $where . "'";
        $item_data = $this->getCustom($item_query);
        return $item_data;
    }

    /* get order item for service end */


    /* get order item for proposal start */

    function or_item_p($where) {
        $item_query = 'SELECT * FROM `tbl_orders` inner join tbl_proposal on tbl_orders.pr_id=tbl_proposal.pr_id where tbl_orders.or_no =' . "'" . $where . "'";
        $item_data = $this->getCustom($item_query);
        return $item_data;
    }

    /* get order item for proposal end */

    /* get buyer order data start */

    function order_data_buyer($where) {
        $order_query = 'SELECT * FROM (SELECT * FROM  `tbl_orders` inner join tbl_conversation_reply on tbl_orders.or_id=tbl_conversation_reply.cr_order inner  join tbl_users on tbl_conversation_reply.cr_sender = tbl_users.id where tbl_orders.user_id = ' . $where . '  and tbl_conversation_reply.cr_sender != ' . $where . ' order by tbl_conversation_reply.cr_id DESC)as temp_table group by or_id';
        $order_data = $this->getCustom($order_query);
        return $order_data;
    }

    /* get buyer order data end */

    /* get seller order data from proposal start */

    function seller_order_data_p($where) {
        $order_query = 'SELECT * FROM (SELECT tbl_orders.*,tbl_conversation_reply.*,tbl_users.* FROM `tbl_orders` inner join tbl_proposal on  tbl_orders.pr_id=tbl_proposal.pr_id inner join tbl_conversation_reply on  tbl_orders.or_id=tbl_conversation_reply.cr_order inner join tbl_users on  tbl_conversation_reply.cr_sender=tbl_users.id where tbl_proposal.user_id = ' . $where . ' and tbl_conversation_reply.cr_sender != ' . $where . ' order by tbl_conversation_reply.cr_id desc)as temp_table group by or_id';
        $order_data = $this->getCustom($order_query);
        return $order_data;
    }

    /* get seller order data from proposal end */

    /* get seller order data from service start */

    function seller_order_data_s($where) {
        $order_query = 'SELECT * FROM (SELECT tbl_orders.*,tbl_conversation_reply.*,tbl_users.* FROM `tbl_orders` inner join tbl_services on  tbl_orders.service_id=tbl_services.id inner join tbl_conversation_reply on  tbl_orders.or_id=tbl_conversation_reply.cr_order inner join tbl_users on  tbl_conversation_reply.cr_sender=tbl_users.id where tbl_services.user_id = ' . $where . '  and tbl_conversation_reply.cr_sender !=' . $where . ' order by tbl_conversation_reply.cr_id DESC)as temp_table group by or_id';
        $order_data = $this->getCustom($order_query);
        return $order_data;
    }

    /* get seller order data from service end */



    /* get  request detail start */

    function getReqDetail($where) {
        $request_detail = $this->getCustom('SELECT * FROM `tbl_product_request` inner join tbl_categories on tbl_product_request.br_category=tbl_categories.cat_id inner join tbl_uom on tbl_product_request.br_uom=tbl_uom.uom_id inner join tbl_users on tbl_product_request.user_id=tbl_users.user_id where ' . $where);
        return $request_detail;
    }

    /* get  request detail end */


    /* count recent request start */

    function countReReq($where) {
        $recent_request = $this->getCustom('SELECT count(tbl_product_request.br_id) as total_requests FROM `tbl_product_request` left outer join tbl_proposal on tbl_product_request.br_id = tbl_proposal.br_id INNER JOIN tbl_users ON tbl_product_request.user_id = tbl_users.user_id where ' . $where);
        return $recent_request;
    }

    /* count recent request end */

    function getLoginDetails($tablename, $username, $password) {
        $this->db->where('tbl_users.user_name', $username);
        $this->db->where('tbl_users.password', $password);
        $this->db->or_where('tbl_users.user_email', $username);
        $this->db->where('tbl_users.password', $password);
        $getd = $this->db->get($tablename);
        $data = $getd->row();
        if (count($data) > 0) {
            return $data;
        } else {
            return false;
        }
    }

    function getLimited($table, $from, $to) {
        $getdata = $this->db->get($table, $from, $to);
        $num = $getdata->num_rows();
        if ($num > 0) {
            $arr = $getdata->result();
            foreach ($arr as $rows) {
                $data[] = $rows;
            }
            $getdata->free_result();
            return $data;
        } else {
            return false;
        }
    }

    //Fetch limited data end



    /* Count requests function with where class start */
    function countAllRequests($tbl1, $tbl2, $field1, $field2, $where) {
        $this->db->select('COUNT(' . $tbl1 . '.rs_id' . ') AS total_requests');
        $this->db->from($tbl1);
        $this->db->join($tbl2, $tbl1 . '.' . $field1 . '=' . $tbl2 . '.' . $field2);
        $this->db->where($where);

        $getdata = $this->db->get();
        $num = $getdata->num_rows();
        if ($num > 0) {
            $arr = $getdata->result();
            foreach ($arr as $rows) {
                $data[] = $rows;
            }
            $getdata->free_result();
            return $data;
        } else {
            return false;
        }
    }

    /* Count request function with where class end */









    /* Count function with where class start */

    function countAllServices($tbl1, $tbl2, $field1, $field2, $where) {
        $this->db->select('COUNT(' . $tbl1 . '.id' . ') AS total_services');
        $this->db->from($tbl1);
        $this->db->join($tbl2, $tbl1 . '.' . $field1 . '=' . $tbl2 . '.' . $field2);
        $this->db->where($where);

        $getdata = $this->db->get();
        $num = $getdata->num_rows();
        if ($num > 0) {
            $arr = $getdata->result();
            foreach ($arr as $rows) {
                $data[] = $rows;
            }
            $getdata->free_result();
            return $data;
        } else {
            return false;
        }
    }

    /* Count function with where class end */

    /* count id with where start */

    function countAllId($tbl1, $field1, $where) {
        $this->db->select('COUNT(' . $tbl1 . '.' . $field1 . ') as total');
        $this->db->from($tbl1);
        $this->db->where($where);

        $getdata = $this->db->get();
        $num = $getdata->num_rows();
        if ($num > 0) {
            $arr = $getdata->result();
            foreach ($arr as $rows) {
                $data[] = $rows;
            }
            $getdata->free_result();
            return $data;
        } else {
            return false;
        }
    }

    /* count id with where end */

    //Fetch data using join two tables with limit  start
    function getJoinTwo($tbl1, $tbl2, $field1, $field2, $where, $limit, $offset) {

        $this->db->select($tbl1 . '.*,' . $tbl2 . '.f_name,' . $tbl2 . '.id as user_id');
        $this->db->from($tbl1);
        $this->db->join($tbl2, $tbl1 . '.' . $field1 . '=' . $tbl2 . '.' . $field2);
        $this->db->where($where);
        $this->db->limit($limit, $offset);
        $getdata = $this->db->get();
        $num = $getdata->num_rows();
        if ($num > 0) {
            $arr = $getdata->result();
            foreach ($arr as $rows) {
                $data[] = $rows;
            }
            $getdata->free_result();
            return $data;
        } else {
            return false;
        }
    }

    //Fetch data using join two tables with limit end

    /* get date difference start */
    function dateDiff($time1, $time2, $precision = 6) {
        // If not numeric then convert texts to unix timestamps
        if (!is_int($time1)) {
            $time1 = strtotime($time1);
        }
        if (!is_int($time2)) {
            $time2 = strtotime($time2);
        }

        // If time1 is bigger than time2
        // Then swap time1 and time2
        if ($time1 > $time2) {
            $ttime = $time1;
            $time1 = $time2;
            $time2 = $ttime;
        }

        // Set up intervals and diffs arrays
        $intervals = array('year', 'month', 'day', 'hour', 'minute', 'second');
        $diffs = array();

        // Loop thru all intervals
        foreach ($intervals as $interval) {
            // Create temp time from time1 and interval
            $ttime = strtotime('+1 ' . $interval, $time1);
            // Set initial values
            $add = 1;
            $looped = 0;
            // Loop until temp time is smaller than time2
            while ($time2 >= $ttime) {
                // Create new temp time from time1 and interval
                $add++;
                $ttime = strtotime("+" . $add . " " . $interval, $time1);
                $looped++;
            }

            $time1 = strtotime("+" . $looped . " " . $interval, $time1);
            $diffs[$interval] = $looped;
        }

        $count = 0;
        $times = array();
        // Loop thru all diffs
        foreach ($diffs as $interval => $value) {
            // Break if we have needed precission
            if ($count >= $precision) {
                break;
            }
            // Add value and interval 
            // if value is bigger than 0
            if ($value > 0) {
                // Add s if value is not 1
                if ($value != 1) {
                    $interval .= "s";
                }
                // Add value and interval to times array
                $times[] = $value . " " . $interval;
                $count++;
            }
        }

        // Return string with times
        return implode(", ", $times);
    }

    /* get date difference end */




    /* get all data with left join and where clause start */

    function getLJTAndW($tbl1, $tbl2, $field1, $field2, $where) {
        $this->db->select('*');
        $this->db->from($tbl1);
        $this->db->join($tbl2, $tbl1 . '.' . $field1 . '=' . $tbl2 . '.' . $field2, 'left outer');
        $this->db->where($where);

        $getdata = $this->db->get();
        $num = $getdata->num_rows();
        if ($num > 0) {
            $arr = $getdata->result();
            foreach ($arr as $rows) {
                $data[] = $rows;
            }
            $getdata->free_result();
            return $data;
        } else {
            return false;
        }
    }

    /* get all data with left join and where clause end */

    /* get rating for product start */

    function product_rating($product_id) {
        $review_rate = $this->get_product_review($product_id);
        $total_feedback = $this->getCustom('SELECT count(rev_id) as total_review FROM `tbl_reviews` inner join tbl_orders on tbl_reviews.rev_or_no=tbl_orders.or_no where tbl_orders.or_product = ' . $product_id);

        $rating_cls = '';
        if ($review_rate[0]->rev_rate != '') {
            if ($review_rate[0]->rev_rate > 0 && $review_rate[0]->rev_rate <= 0.5) {
                $rating_cls = 'rating1';
            } else if ($review_rate[0]->rev_rate > 0.5 && $review_rate[0]->rev_rate <= 1) {
                $rating_cls = 'rating2';
            } else if ($review_rate[0]->rev_rate > 1 && $review_rate[0]->rev_rate <= 1.5) {
                $rating_cls = 'rating3';
            } else if ($review_rate[0]->rev_rate > 1.5 && $review_rate[0]->rev_rate <= 2) {
                $rating_cls = 'rating4';
            } else if ($review_rate[0]->rev_rate > 2 && $review_rate[0]->rev_rate <= 2.5) {
                $rating_cls = 'rating5';
            } else if ($review_rate[0]->rev_rate > 2.5 && $review_rate[0]->rev_rate <= 3) {
                $rating_cls = 'rating6';
            } else if ($review_rate[0]->rev_rate > 3 && $review_rate[0]->rev_rate <= 3.5) {
                $rating_cls = 'rating7';
            } else if ($review_rate[0]->rev_rate > 3.5 && $review_rate[0]->rev_rate <= 4) {
                $rating_cls = 'rating8';
            } else if ($review_rate[0]->rev_rate > 4 && $review_rate[0]->rev_rate <= 4.5) {
                $rating_cls = 'rating9';
            } else if ($review_rate[0]->rev_rate > 4.5 && $review_rate[0]->rev_rate <= 5) {
                $rating_cls = 'rating10';
            } else {
                $rating_cls = 'rating0';
            }
        } else {
            $rating_cls = 'rating0';
        }

        $rating_string = '<div class="rating">
		<div class="' . $rating_cls . '">
		</div>
		</div>
		<span class="font14 colorGray">Feedback(' . $total_feedback[0]->total_review . ') </span>';

        return $rating_string;
    }

    /* get rating for product end */

    /* get user rating from service start */

    function get_user_rating($user_id) {
        /* get rating data from product start */
        $rating_query_s = 'SELECT round(avg(rev_rate),1) as rev_rate FROM `tbl_reviews` inner join tbl_orders on tbl_reviews.rev_or_no=tbl_orders.or_no inner join tbl_products on tbl_orders.or_product=tbl_products.p_id where tbl_products.p_user = ' . $user_id;
        $rating_s_data = $this->getCustom($rating_query_s);
        /* get rating data from product end */

        /* get rating data from services start */
        $rating_query_p = 'SELECT round(avg(rev_rate),1) as rev_rate FROM `tbl_reviews` inner join tbl_orders on tbl_reviews.rev_or_no=tbl_orders.or_no inner join tbl_proposal on tbl_orders.or_pr_id=tbl_proposal.pr_id where tbl_proposal.user_id = ' . $user_id;
        $rating_p_data = $this->getCustom($rating_query_p);
        /* get rating data from services end */

        /* get total feedback start */
        $total_feedback = $this->getCustom('SELECT count(rev_id) as total_review FROM `tbl_reviews` where rev_user_t = ' . $user_id);
        /* get total feedback end */

        $review_rate = '';
        if ($rating_s_data[0]->rev_rate && $rating_p_data[0]->rev_rate == '') {
            $review_rate = $rating_s_data[0]->rev_rate;
        } else if ($rating_p_data[0]->rev_rate && $rating_s_data[0]->rev_rate == '') {
            $review_rate = $rating_p_data[0]->rev_rate;
        } else if ($rating_p_data[0]->rev_rate && $rating_s_data[0]->rev_rate) {
            $review_rate = ($rating_p_data[0]->rev_rate + $rating_s_data[0]->rev_rate) / 2;
        } else {
            $review_rate = 0;
        }

        $rating_cls = '';

        if ($review_rate > 0 && $review_rate <= 0.5) {
            $rating_cls = 'rating1';
        } else if ($review_rate > 0.5 && $review_rate <= 1) {
            $rating_cls = 'rating2';
        } else if ($review_rate > 1 && $review_rate <= 1.5) {
            $rating_cls = 'rating3';
        } else if ($review_rate > 1.5 && $review_rate <= 2) {
            $rating_cls = 'rating4';
        } else if ($review_rate > 2 && $review_rate <= 2.5) {
            $rating_cls = 'rating5';
        } else if ($review_rate > 2.5 && $review_rate <= 3) {
            $rating_cls = 'rating6';
        } else if ($review_rate > 3 && $review_rate <= 3.5) {
            $rating_cls = 'rating7';
        } else if ($review_rate > 3.5 && $review_rate <= 4) {
            $rating_cls = 'rating8';
        } else if ($review_rate > 4 && $review_rate <= 4.5) {
            $rating_cls = 'rating9';
        } else if ($review_rate > 4.5 && $review_rate <= 5) {
            $rating_cls = 'rating10';
        } else {
            $rating_cls = 'rating0';
        }

        $rating_string = '<div class="rating">
                            <div class="' . $rating_cls . '"></div>
                        </div>
                        <span class="font14 colorGray">Feedback(' . $total_feedback[0]->total_review . ') </span>';

        return $rating_string;
    }

    /* get user rating from service end */

    /* get Product data start */

    function getProductData($where) {
        $product_detail = $this->getCustom('SELECT * FROM `tbl_products`  inner join tbl_uom on tbl_products.p_uom=tbl_uom.uom_id inner join tbl_pay_term on tbl_products.p_pay_term=tbl_pay_term.pa_te_id inner join tbl_pri_term on tbl_products.p_price_term=tbl_pri_term.pr_te_id inner join tbl_users on tbl_products.p_user=tbl_users.user_id where ' . $where);
        return $product_detail;
    }

    /* get service data end */



    /* get multiple records from each group start */

    function getProductsGroupWise() {
        $str_query = 'SELECT x.* FROM (SELECT bp.*, CASE WHEN bp.p_cat = @p_cat THEN @rownum := @rownum + 1 ELSE @rownum := 1 END AS rank, @p_cat := bp.p_cat
       FROM (select tbl_products.*,tbl_categories.cat_name from tbl_products join tbl_categories on tbl_categories.cat_id=tbl_products.p_cat where tbl_categories.populer_status=2 and tbl_products.p_status!=6 and tbl_products.p_status!=5 order by rand()) bp
       JOIN (SELECT @rownum := 0, @p_cat := NULL) r
       ORDER BY p_cat ASC) x
        WHERE x.rank <= 4
       order by x.cat_name ASC';
        $requests = $this->getCustom($str_query);
        return $requests;
    }

    /* get multiple records from each group end */

    /* get products start */

    function getProducts($where = null) {
        $str_query = '';
        if (!isset($where)) {
            $str_query = 'Select tbl_products.*,tbl_categories.cat_name,tbl_uom.uom_name,(select cat_name from tbl_categories where cat_id=tbl_products.p_scat ) as scat_name,tbl_users.user_name from tbl_products inner join tbl_categories on tbl_categories.cat_id=tbl_products.p_cat inner join tbl_users on tbl_users.user_id=tbl_products.p_user inner join tbl_uom on tbl_uom.uom_id=tbl_products.p_uom where tbl_products.p_status!=6 and tbl_products.p_status!=5 order by p_datetime DESC';
        } else {
            $str_query = 'Select tbl_products.*,tbl_categories.cat_name,tbl_uom.uom_name,(select cat_name from tbl_categories where cat_id=tbl_products.p_scat ) as scat_name,tbl_users.user_name from tbl_products inner join tbl_categories on tbl_categories.cat_id=tbl_products.p_cat inner join tbl_users on tbl_users.user_id=tbl_products.p_user inner join tbl_uom on tbl_uom.uom_id=tbl_products.p_uom where ' . $where . '';
        }
        
        $products = $this->getCustom($str_query);
        return $products;
    }

    /* get products end */





    /* get all data with join and where clause */

    function getJoinTwoAndWhere($tbl1, $tbl2, $field1, $field2, $where) {

        $this->db->select($tbl1 . '.*,' . $tbl2 . '.*');
        $this->db->from($tbl1);
        $this->db->join($tbl2, $tbl1 . '.' . $field1 . '=' . $tbl2 . '.' . $field2);
        $this->db->where($where);
        //$this->db->limit($limit, $offset);
        $getdata = $this->db->get();
        $num = $getdata->num_rows();
        if ($num > 0) {
            $arr = $getdata->result();
            foreach ($arr as $rows) {
                $data[] = $rows;
            }
            $getdata->free_result();
            return $data;
        } else {
            return false;
        }
    }

    /**/

    /* get request in desc start */

    function getReReqWithO($tbl1, $tbl2, $field1, $field2, $where) {

        $this->db->select($tbl1 . '.*,' . $tbl2 . '.f_name,' . $tbl2 . '.l_name,' . $tbl2 . '.reg_date,' . $tbl2 . '.user_cn,' . $tbl2 . '.user_cid');
        $this->db->from($tbl1);
        $this->db->join($tbl2, $tbl1 . '.' . $field1 . '=' . $tbl2 . '.' . $field2);
        $this->db->order_by("rs_id", "desc");
        $this->db->where($where);
        //$this->db->limit($limit, $offset);
        $getdata = $this->db->get();
        $num = $getdata->num_rows();
        if ($num > 0) {
            $arr = $getdata->result();
            foreach ($arr as $rows) {
                $data[] = $rows;
            }
            $getdata->free_result();
            return $data;
        } else {
            return false;
        }
    }

    /* get request in desc end */





    /* get all data with join and where and like start */

    function getJTAndWAndL($tbl1, $tbl2, $field1, $field2, $where, $like_field, $like_txt) {

        $this->db->select($tbl1 . '.*,' . $tbl2 . '.f_name,' . $tbl2 . '.l_name');
        $this->db->from($tbl1);
        $this->db->join($tbl2, $tbl1 . '.' . $field1 . '=' . $tbl2 . '.' . $field2);
        $this->db->where($where);
        $this->db->like($like_field, $like_txt);
        $getdata = $this->db->get();
        $num = $getdata->num_rows();
        if ($num > 0) {
            $arr = $getdata->result();
            foreach ($arr as $rows) {
                $data[] = $rows;
            }
            $getdata->free_result();
            return $data;
        } else {
            return false;
        }
    }

    /* get all data with join and where and like start */

    /* get buyer services data start */

    function getBuySer($tbl1, $tbl2, $field1, $field2, $where) {
        $this->db->select($tbl1 . '.*,' . $tbl2 . '.*');
        $this->db->from($tbl1);
        $this->db->join($tbl2, $tbl1 . '.' . $field1 . '=' . $tbl2 . '.' . $field2);
        $this->db->where($where);
        $this->db->order_by($tbl1 . ".or_id", "desc");

        $getdata = $this->db->get();
        $num = $getdata->num_rows();
        if ($num > 0) {
            $arr = $getdata->result();
            foreach ($arr as $rows) {
                $data[] = $rows;
            }
            $getdata->free_result();
            return $data;
        } else {
            return false;
        }
    }

    /* get buyer services data end */

    /* get sell services data start */

    function getSellSer($tbl1, $tbl2, $tbl3, $field1, $field2, $field3, $where) {
        $this->db->select($tbl1 . '.*,' . $tbl2 . '.id as service_id,' . $tbl2 . '.service_title,' . $tbl2 . '.service_duration,' . $tbl3 . '.id as user_id,' . $tbl3 . '.f_name,' . $tbl3 . '.l_name,' . $tbl3 . '.user_photo');
        $this->db->from($tbl1);
        $this->db->join($tbl2, $tbl1 . '.' . $field1 . '=' . $tbl2 . '.' . $field2);
        $this->db->join($tbl3, $tbl1 . '.' . $field3 . '=' . $tbl3 . '.' . $field2);
        $this->db->where($where);
        $this->db->order_by($tbl1 . ".or_id", "desc");

        $getdata = $this->db->get();
        $num = $getdata->num_rows();
        if ($num > 0) {
            $arr = $getdata->result();
            foreach ($arr as $rows) {
                $data[] = $rows;
            }
            $getdata->free_result();
            return $data;
        } else {
            return false;
        }
    }

    /* get sell services data end */



    /* get join three with order and where start */

    function getJThAndOW($tbl1, $tbl2, $tbl3, $field1, $field2, $field3, $order_field, $where) {

        $this->db->select('*');
        $this->db->from($tbl1);
        $this->db->join($tbl2, $tbl1 . '.' . $field1 . '=' . $tbl2 . '.' . $field1);
        $this->db->join($tbl3, $tbl1 . '.' . $field2 . '=' . $tbl3 . '.' . $field3);
        $this->db->order_by($order_field, "desc");
        $this->db->where($where);
        //$this->db->limit($limit, $offset);
        $getdata = $this->db->get();
        $num = $getdata->num_rows();
        if ($num > 0) {
            $arr = $getdata->result();
            foreach ($arr as $rows) {
                $data[] = $rows;
            }
            $getdata->free_result();
            return $data;
        } else {
            return false;
        }
    }

    /* get inbox data start */

    function getConData($tbl1, $tbl2, $tbl3, $field1, $field2, $field3, $field4, $order_field, $where) {

        $this->db->select('distinct(' . $tbl1 . '.' . $field1 . '),' . $tbl1 . '.*,' . $tbl3 . '.*,' . $tbl2 . '.*');
        $this->db->from($tbl1);
        $this->db->join($tbl2, $tbl1 . '.' . $field1 . '=' . $tbl2 . '.' . $field2);
        $this->db->join($tbl3, $tbl1 . '.' . $field3 . '=' . $tbl3 . '.' . $field4);
        $this->db->group_by($tbl1 . "." . $field1);
        $this->db->order_by($order_field, "DESC");
        $this->db->where($where);
        //$this->db->limit($limit, $offset);
        $getdata = $this->db->get();
        $num = $getdata->num_rows();
        if ($num > 0) {
            $arr = $getdata->result();
            foreach ($arr as $rows) {
                $data[] = $rows;
            }
            $getdata->free_result();
            return $data;
        } else {
            return false;
        }
    }

    /* get inbox data end */



    /* get image size start */

    function byte_convert($size) {
        # size smaller then 1kb
        if ($size < 1024)
            return $size . ' Byte';
        # size smaller then 1mb
        if ($size < 1048576)
            return sprintf("%4.2f KB", $size / 1024);
        # size smaller then 1gb
        if ($size < 1073741824)
            return sprintf("%4.2f MB", $size / 1048576);
        # size smaller then 1tb
        if ($size < 1099511627776)
            return sprintf("%4.2f GB", $size / 1073741824);
        # size larger then 1tb
        else
            return sprintf("%4.2f TB", $size / 1073741824);
    }

    /* get image size end */

    /* get Conversation data start */

    function get_ConData($where) {
        $con_query = 'SELECT * FROM ( SELECT `tbl_conversation_reply`.*, `tbl_users`.*, `tbl_conversation`.* FROM (`tbl_conversation_reply`) JOIN `tbl_conversation` ON `tbl_conversation_reply`.`c_id`=`tbl_conversation`.`con_id` JOIN `tbl_users` ON `tbl_conversation_reply`.`cr_sender`=`tbl_users`.`user_id` WHERE (tbl_conversation.con_sender =' . $where . ' or tbl_conversation.con_reciever =' . $where . ') and tbl_conversation_reply.cr_sender !=' . $where . ' and tbl_conversation_reply.cr_order="" and tbl_conversation_reply.cr_auction=0 ORDER BY `tbl_conversation_reply`.`cr_id` DESC )as temp_table GROUP BY c_id ';
        $con_data = $this->getCustom($con_query);
        return $con_data;
    }

    /* get Conversation data end */

    /* get Conversation header data start */

    function get_ConHeaderData($where) {
        $con_query = 'SELECT distinct(tbl_conversation_reply.c_id), `tbl_conversation_reply`.*, `tbl_users`.*, `tbl_conversation`.*,tbl_orders.* FROM (`tbl_conversation_reply`) JOIN `tbl_conversation` ON `tbl_conversation_reply`.`c_id`=`tbl_conversation`.`con_id` JOIN `tbl_users` ON `tbl_conversation_reply`.`cr_sender`=`tbl_users`.`user_id` left join tbl_orders on tbl_orders.or_id=tbl_conversation_reply.cr_order WHERE (tbl_conversation.con_sender = ' . $where . ' or tbl_conversation.con_reciever = ' . $where . ') and tbl_conversation_reply.cr_sender !=' . $where . ' and tbl_conversation_reply.cr_status=0 ORDER BY `tbl_conversation_reply`.`cr_id` DESC';
        $con_data = $this->getCustom($con_query);
        return $con_data;
    }

    /* get Conversation header data end */

    /* get All Conversation header data start */

    function get_AllConHeaderData($where) {
        $con_query = 'SELECT distinct(tbl_conversation_reply.c_id), `tbl_conversation_reply`.*, `tbl_users`.*, `tbl_conversation`.*,tbl_orders.* FROM (`tbl_conversation_reply`) JOIN `tbl_conversation` ON `tbl_conversation_reply`.`c_id`=`tbl_conversation`.`con_id` JOIN `tbl_users` ON `tbl_conversation_reply`.`cr_sender`=`tbl_users`.`user_id` left join tbl_orders on tbl_orders.or_id=tbl_conversation_reply.cr_order WHERE (tbl_conversation.con_sender = ' . $where . ' or tbl_conversation.con_reciever = ' . $where . ') and tbl_conversation_reply.cr_sender !=' . $where . ' ORDER BY `tbl_conversation_reply`.`cr_id` DESC';
        $con_data = $this->getCustom($con_query);
        return $con_data;
    }

    /* get All Conversation header data end */

    /* get header conversation data start */

    function getConHeaderData($tbl1, $tbl2, $tbl3, $field1, $field2, $field3, $field4, $order_field, $where) {

        $this->db->select('distinct(' . $tbl1 . '.' . $field1 . '),' . $tbl1 . '.*,' . $tbl3 . '.*,' . $tbl2 . '.*');
        $this->db->from($tbl1);
        $this->db->join($tbl2, $tbl1 . '.' . $field1 . '=' . $tbl2 . '.' . $field2);
        $this->db->join($tbl3, $tbl1 . '.' . $field3 . '=' . $tbl3 . '.' . $field4);
        // $this->db->group_by($tbl1.".".$field1); 
        $this->db->order_by($order_field, "DESC");
        $this->db->where($where);
        //$this->db->limit($limit, $offset);
        $getdata = $this->db->get();
        $num = $getdata->num_rows();
        if ($num > 0) {
            $arr = $getdata->result();
            foreach ($arr as $rows) {
                $data[] = $rows;
            }
            $getdata->free_result();
            return $data;
        } else {
            return false;
        }
    }
    /* get header conversation data end */
    
    
    /**
     * 
     * @return type
     */
    function getServicesList(){
         $str_query = '';
        
            $str_query = 'Select * from `tbl_categories` where `parent_id`=218';
       
        $service = $this->getCustom($str_query);
        return $service;
    }
    
   
    
    /**
     * 
     * @param type $form_data
     * @return boolean
     */
    
    function RegisterCompany($form_data)
	{
		$this->db->insert('tbl_company_register', $form_data);
		
		if ($this->db->affected_rows() == '1')
		{
			return TRUE;
		}
		
		return FALSE;
	}
        /**
         * @Shamx
         * @return Array
         * 
         */
        function getCompanyDir(){
            $str_query = '';
        
            $str_query = 'SELECT * FROM `tbl_company_register` as cr inner join `tbl_categories` as tc on(cr.industry_type=tc.cat_id) order by cr.industry_type ASC';
       
        $companyDir = $this->getCustom($str_query);
        return $companyDir;
        }
        /**
         * @Awais
         * @return Row
         * 
         */
        function getCompanyDetails($companyId){
            $str_query = '';
        
            $str_query = 'SELECT * FROM `tbl_company_register` as cr inner join `tbl_categories` as tc on(cr.industry_type=tc.cat_id) WHERE id=\''.$companyId.' \'';
       
        $companyDir = $this->getCustom($str_query);
        return $companyDir;
        }
}
