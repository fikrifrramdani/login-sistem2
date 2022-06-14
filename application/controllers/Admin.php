<?php defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{
   public function index()
   {
      $data['title'] = 'Dashboard';
      $data['user'] = $this->Model_user->view_user()->row_array();
      $data['menu'] = $this->Model_menu->getMenu();

      $this->load->view('templates/user_header', $data);
      $this->load->view('templates/user_sidebar', $data);
      $this->load->view('templates/user_topbar', $data);
      $this->load->view('admin/index', $data);
      $this->load->view('templates/user_footer');
   }

   public function role()
   {
      $data['title'] = 'Role';
      $data['user'] = $this->Model_user->view_user()->row_array();
      $data['role'] = $this->Model_user->getUser()->result_array();

      $this->form_validation->set_rules('role', 'Role', 'required');

      $data['menu'] = $this->Model_menu->getMenu();

      if ($this->form_validation->run() == false) {
         $this->load->view('templates/user_header', $data);
         $this->load->view('templates/user_sidebar', $data);
         $this->load->view('templates/user_topbar', $data);
         $this->load->view('admin/role', $data);
         $this->load->view('templates/user_footer');
      } else {
         $data = [
            'role' => $this->input->post('role')
         ];

         $this->Model_user->addRole($data, 'user_role');

         $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">New Role Added</div>');
         redirect('admin/role');
      }
   }

   public function roleAccess($role_id)
   {
      $data['title'] = 'Role Access';
      $data['user'] = $this->Model_user->view_user()->row_array();

      $data['role'] = $this->db->get_where('user_role', ['id' => $role_id])->row_array();

      $data['menu'] = $this->db->get('user_menu')->result_array();

      $data['menu'] = $this->Model_menu->getMenu();

      $this->load->view('templates/user_header', $data);
      $this->load->view('templates/user_sidebar', $data);
      $this->load->view('templates/user_topbar', $data);
      $this->load->view('admin/roleAccess', $data);
      $this->load->view('templates/user_footer');
   }

   public function changeAccess()
   {
      $menu_id = $this->input->post('menuId');
      $role_id = $this->input->post('roleId');

      $data = [
         'role_id' => $role_id,
         'menu_id' => $menu_id
      ];

      $result = $this->db->get_where('user_access_menu', $data);

      if ($result->num_rows() < 1) {
         $this->db->insert('user_access_menu', $data);
      } else {
         $this->db->delete('user_access_menu', $data);
      }

      $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Access Changed!</div>');
   }

   public function deleteRole($id)
   {
      $where = [
         'id' => $id
      ];

      $this->Model_user->hapusRole($where, 'user_role');
      redirect('admin/role');
   }

   public function backupDb()
   {
      $data['user'] = $this->Model_user->view_user()->row_array();

      $data['menu'] = $this->db->get('user_menu')->result_array();

      $data['menu'] = $this->Model_menu->getMenu();
      $data['title'] = 'Backup Database';

      $this->load->view('templates/user_header', $data);
      $this->load->view('templates/user_sidebar', $data);
      $this->load->view('templates/user_topbar', $data);
      $this->load->view('admin/backupDb', $data);
      $this->load->view('templates/user_footer');
   }

   public function backup()
   {
      date_default_timezone_set("Asia/Jakarta"); // set waktu sesuai lokasi

      $this->load->dbutil();
      $name    = 'backup__' . date("d-m-Y__H-i-s") . '.zip'; // nama backup dalam bentuk zip

      $pref = [
         'format' => 'zip',
         'filename' => $name
      ];

      $backup     = $this->dbutil->backup($pref);
      $db_name    = 'backup_database__' . date("d-m-Y__H-i-s") . '.zip'; // nama backup dalam bentuk zip
      $save       = './backup/db/' . $db_name; //folder tempat database disimpan

      write_file($save, $backup);

      force_download($db_name, $backup);
   }
}
