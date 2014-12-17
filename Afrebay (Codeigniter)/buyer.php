<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Buyer extends CI_Controller {

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     * 	- or -  
     * 		http://example.com/index.php/welcome/index
     * 	- or -
     * Since this controller is set as the default controller in 
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see http://codeigniter.com/user_guide/general/urls.html
     */
    function __construct() {
        parent::__construct();
        if ($this->session->userdata('user_id') == '' || $this->session->userdata('activation_status') != 1) {
            redirect();
            exit();
        }
        $this->load->model('home_m');
    }

    public function index() {
        /* All industry data start */
        $where_idi = array('status' => '0');
        $config['getAllIndustry'] = $this->home_m->getWhere('tbl_industry', $where_idi);
        /* All industry data end */

        /* All Category data start */
        $where_idc = array('parent_id' => '0');
        $config['getAllMainCateg'] = $this->home_m->getSpecificCat('tbl_categories', $where_idc);
        /* All Category data end */

        /* All Sub-category data start */
        $where_ids = array('parent_id !=' => '0');
        $config['getAllSubCateg'] = $this->home_m->getSpecificCat('tbl_categories', $where_ids);
        /* All Sub-category data end */

        /* user data start */
        $arr_UId = array('id' => $this->session->userdata('user_id'));
        $config['userData'] = $this->home_m->getWhere('tbl_users', $arr_UId);
        /* user data end */

        /* footer recent request data start */
        $where_request = array('rs_status =' => '1');
        $config['getFoReReq'] = $this->home_m->getReReqWithO('tbl_service_request', 'tbl_users', 'user_id', 'id', $where_request);
        /* footer recent request data end */



        $this->session->set_userdata('title', 'Sell Service ');

        $this->load->view('header', $config);
        $this->load->view('sell_services', $config);
        $this->load->view('footer');
    }

    /* show buyer activity start */

    public function activity() {
        $user_id = $this->session->userdata('user_id');

        /* get page required data start */
        $config = $this->home_m->page_req_data();
        /* get page required data end */


        /* get all request start */
        $config['requestData'] = $this->home_m->get_all_request($user_id);
        /* get all request end */

        /* get conversation id  start */
        $config['convData'] = $this->home_m->get_ConData($user_id);
        /* get conversation id  end */


        /* set request count start */
        $this->session->set_userdata('user_request_count', 0);
        /* set request count end */

        /* set conversation count start */
        $this->session->set_userdata('conv_count', 0);
        /* set conversation count end */


        /* set buyer order count start */
        $this->session->set_userdata('or_b_count', 0);
        /* set buyer order count start */

        /* set title start */
        $this->session->set_userdata('title', 'Buyer Activity');
        /* set title end */

        $this->load->view('header', $config);
        $this->load->view('buyer_activity');
        $this->load->view('footer');
    }

    /* show buyer activity start */





    /* show payment for buyer start */

    public function buy() {
        $user_id = $this->session->userdata('user_id');

        /* get conversation header data start */
        $where_conversation = $user_id;
        $config['convHeaderData'] = $this->home_m->get_ConHeaderData($where_conversation);
        /* get conversation header data end */

        /* set conversation count start */
        $this->session->set_userdata('conv_count', 0);
        /* set conversation count end */

        /* All industry data start */
        $where_idi = array('status' => '0');
        $config['getAllIndustry'] = $this->home_m->getWhere('tbl_industry', $where_idi);
        /* All industry data end */

        /* All Category data start */
        $where_idc = array('parent_id' => '0');
        $config['getAllMainCateg'] = $this->home_m->getSpecificCat('tbl_categories', $where_idc);
        /* All Category data end */

        /* All Sub-category data start */
        $where_ids = array('parent_id !=' => '0');
        $config['getAllSubCateg'] = $this->home_m->getSpecificCat('tbl_categories', $where_ids);
        /* All Sub-category data end */

        /* user data start */
        $arr_UId = array('id' => $this->session->userdata('user_id'));
        $config['userData'] = $this->home_m->getWhere('tbl_users', $arr_UId);
        /* user data end */

        /* footer recent request data start */
        $where_request = array('rs_status =' => '1');
        $config['getFoReReq'] = $this->home_m->getReReqWithO('tbl_service_request', 'tbl_users', 'user_id', 'id', $where_request);
        /* footer recent request data end */

        /* all request data start */
        $where_request = array('user_id' => $user_id, 'rs_status !=' => 2);
        $config['getAllRequest'] = $this->home_m->getWhereWithO('tbl_service_request', $where_request, 'rs_id');
        /* all request data start */

        /* get order data start */
        $where_order = array('user_id' => $user_id);
        $config['orderData'] = $this->home_m->getWhereWithO('tbl_orders', $where_order, 'or_id');
        /* get order data end */

        $this->session->set_userdata('title', 'Balance');

        $this->load->view('header', $config);
        $this->load->view('buy', $config);
        $this->load->view('footer');
    }

    /* show payment for buyer  end */

    /* all required data start */

    public function page_req_data() {
        $user_id = $this->session->userdata('user_id');

        /* All Category data start */
        $where_idc = array('parent_id' => '0');
        $config['getAllMainCateg'] = $this->home_m->getSpecificCat('tbl_categories', $where_idc);
        /* All Category data end */

        /* All Sub-category data start */
        $where_ids = array('parent_id !=' => '0');
        $config['getAllSubCateg'] = $this->home_m->getSpecificCat('tbl_categories', $where_ids);
        /* All Sub-category data end */

        /* user data start */
        $arr_UId = array('id' => $this->session->userdata('user_id'));
        $config['userData'] = $this->home_m->getWhere('tbl_users', $arr_UId);
        /* user data end */


        /* get conversation header data start */

        if ($user_id != '') {
            $where_conversation = $user_id;
            $config['convHeaderData'] = $this->home_m->get_ConHeaderData($where_conversation);
        }

        /* get conversation header data end */

        /* footer recent request data start */
        $where_request = array('rs_status =' => '1');
        $config['getFoReReq'] = $this->home_m->getReReqWithO('tbl_service_request', 'tbl_users', 'user_id', 'id', $where_request);
        /* footer recent request data end */
        return $config;
    }

    /* all required data end */



    /* show cash out page start */

    function cash_out() {
        $config = $this->page_req_data();
        $user_id = $this->session->userdata('user_id');

        /* get cash out  data start */
        $config['cashOutData'] = $this->home_m->get_or_cash_out($user_id);
        /* get cash out  data end */

        /* get configuration data start */
        $where_config = array('confi_id' => 1);
        $config['configData'] = $this->home_m->getWhere('tbl_configuration', $where_config);
        /* get configuration data end */

        /* get transaction data start */
        $where_tran = array('tran_user' => $user_id);
        $config['transData'] = $this->home_m->getWhere('tbl_transaction', $where_tran);
        /* get transaction data end */

        $this->session->set_userdata('title', 'Cash Out');

        $this->load->view('header', $config);
        $this->load->view('cash_out', $config);
        $this->load->view('footer');
    }

    /* show cash out page end */

    /* paypal transfer start */

    function paypal_transfer() {
        $config = $this->page_req_data();
        $config['or_amount'] = $this->input->post('or_amount');
        $this->load->view('header', $config);
        $this->load->view('paypal_transfer', $config);
        $this->load->view('footer');
    }

    /* paypal transfer end */

    /* insert transation start */

    function insertTransaction() {
        $user_id = $this->session->userdata('user_id');
        $arr_transaction = array(
            'tran_user' => $user_id,
            'tran_amount' => $this->input->post('or_amount'),
            'tran_to' => $this->input->post('txt_paypal_id'),
            'tran_datetime' => date('Y-m-d H:i:s'),
            'tran_status' => 0
        );

        $this->home_m->insert('tbl_transaction', $arr_transaction);
        $this->session->set_userdata('header_msg_txt', 'Your payment request was submitted successfully.');
        $this->session->set_userdata('header_msg_type', '0');
        redirect(base_url() . 'balance/cash_out');
    }

    /* insert transation end */
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */