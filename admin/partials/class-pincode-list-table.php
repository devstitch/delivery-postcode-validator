<?php
defined('ABSPATH') || exit;

if (! class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

if (! class_exists('WZC_List_Table')) {
    class WZC_List_Table extends WP_List_Table
    {
        public function __construct()
        {
            parent::__construct(
                array(
                    'singular' => 'singular_form',
                    'plural'   => 'plural_form',
                    'ajax'     => false
                )
            );
        }

        public function prepare_items()
        {
            // Process bulk actions first
            $this->process_bulk_action();

            $columns = $this->get_columns();
            $hidden = [];
            $sortable = $this->get_sortable_columns();

            // Handle search
            $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';

            $data = $this->table_data($search);
            usort($data, [$this, 'sort_data']);

            $per_page = 10;
            $current_page = $this->get_pagenum();
            $total_items = count($data);

            $this->set_pagination_args([
                'total_items' => $total_items,
                'per_page'    => $per_page
            ]);

            $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);
            $this->_column_headers = [$columns, $hidden, $sortable];
            $this->items = $data;
        }

        public function get_bulk_actions()
        {
            return [
                'delete' => 'Delete'
            ];
        }

        public function process_bulk_action()
        {
            global $wpdb;
            $table_name = $wpdb->prefix . 'wzc_postcode';

            // Single item deletion
            if ('delete' === $this->current_action() && isset($_GET['id'])) {
                $id = intval($_GET['id']);

                // Security check
                if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_pincode_' . $id)) {
                    wp_die('Security check failed!');
                }

                $wpdb->delete(
                    $table_name,
                    ['id' => $id],
                    ['%d']
                );

                wp_redirect(admin_url('admin.php?page=view-pin-code'));
                exit;
            }

            // Bulk deletion
            if ('delete' === $this->current_action() && isset($_POST['id'])) {
                // Security check
                if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'bulk-' . $this->_args['plural'])) {
                    wp_die('Security check failed!');
                }

                $delete_ids = array_map('intval', $_POST['id']);

                foreach ($delete_ids as $id) {
                    $wpdb->delete(
                        $table_name,
                        ['id' => $id],
                        ['%d']
                    );
                }

                wp_redirect(admin_url('admin.php?page=view-pin-code'));
                exit;
            }
        }

        public function get_columns()
        {
            return [
                'cb'      => '<input type="checkbox" />',
                'pincode' => 'Pincode',
                'ddays'   => 'Delivery Days',
                'cod'     => 'COD'
            ];
        }

        public function get_hidden_columns()
        {
            return array();
        }

        public function get_sortable_columns()
        {
            return ['pincode' => ['pincode', false]];
        }

        private function table_data($search = '')
        {
            global $wpdb;
            $data = [];

            $where = '';
            if (!empty($search)) {
                $where = $wpdb->prepare(
                    " WHERE wpcc_pincode LIKE %s OR wpcc_days LIKE %s",
                    '%' . $wpdb->esc_like($search) . '%',
                    '%' . $wpdb->esc_like($search) . '%'
                );
            }

            $query = "SELECT * FROM {$wpdb->prefix}wzc_postcode" . $where;
            $records = $wpdb->get_results($query);

            foreach ($records as $record) {
                $data[] = [
                    'id'      => $record->id,
                    'pincode' => $record->wpcc_pincode,
                    'ddays'   => $record->wpcc_days,
                    'cod'     => $record->wpcc_cod == '1' ? 'Yes' : 'No'
                ];
            }

            return $data;
        }

        public function column_default($item, $column_name)
        {
            return $item[$column_name] ?? '';
        }

        private function sort_data($a, $b)
        {
            $orderby = ($_GET['orderby'] ?? 'pincode');
            $order = ($_GET['order'] ?? 'asc');

            $result = strcmp($a[$orderby], $b[$orderby]);
            return ($order === 'asc') ? $result : -$result;
        }

        public function column_cb($item)
        {
            return sprintf('<input type="checkbox" name="id[]" value="%s" />', $item['id']);
        }

        function WPCC_recursive_sanitize_text_field($array)
        {
            foreach ($array as $key => &$value) {
                if (is_array($value)) {
                    $value = $this->WPCC_recursive_sanitize_text_field($value);
                } else {
                    $value = sanitize_text_field($value);
                }
            }
            return $array;
        }

        public function column_pincode($item)
        {
            $actions = [
                'edit' => sprintf(
                    '<a href="?page=add-pin-code&action=edit_pincode&id=%s">Edit</a>',
                    $item['id']
                ),
                'delete' => sprintf(
                    '<a href="?page=%s&action=delete&id=%s&_wpnonce=%s" onclick="return confirm(\'Are you sure you want to delete this item?\')">Delete</a>',
                    $_REQUEST['page'],
                    $item['id'],
                    wp_create_nonce('delete_pincode_' . $item['id'])
                )
            ];

            return sprintf('%1$s %2$s', $item['pincode'], $this->row_actions($actions));
        }
    }
}
