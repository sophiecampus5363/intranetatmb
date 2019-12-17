<?php

    namespace Wpo\Sync;
        
    // Prevent public access to this script
    defined( 'ABSPATH' ) or die();

    use \Wpo\Util\Options;

    if( !class_exists( '\Wpo\Sync\Sync_Admin_Page' ) ) {

        class Sync_Admin_Page {

            public function __construct() {

                // user sync is only possible when subsite options has been activated
                if( is_multisite() 
                    && ( is_network_admin() || is_admin() )
                    && false === Options::mu_use_subsite_options() ) {
                    
                        return;
                }

                if( false === Sync_Db::user_sync_table_exists() ) {
                    Sync_Db::create_user_sync_table();
                }

                $main_blog_id = defined( 'BLOG_ID_CURRENT_SITE' ) ? constant( 'BLOG_ID_CURRENT_SITE' ) : 1;

                // Only allow user synchronization for the main blog
                /* if(  get_current_blog_id() > $main_blog_id )
                    return; */

                add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );

                if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {

                    if( array_key_exists( 'sync_users', $_POST ) ) {

                        Sync_Manager::sync_users();
                    }

                    if( array_key_exists( 'action_create_users', $_POST ) && isset( $_POST[ 'post' ] ) ) {

                        Sync_Manager::create_users( $_POST[ 'post' ] );
                    }

                    if( array_key_exists( 'action_delete_users', $_POST ) && isset( $_POST[ 'post' ] ) ) {

                        Sync_Manager::delete_users( $_POST[ 'post' ] );
                    }

                    if( array_key_exists( 'action_truncate_results', $_POST ) ) {

                        Sync_Manager::delete_job_data();
                    }

                    if( array_key_exists( 'next_link', $_POST ) && array_key_exists( 'settings', $_POST ) ) {

                        Sync_Manager::fetch_users( $_POST[ 'next_link' ], json_decode( urldecode( $_POST[ 'settings' ]) , true ) );
                    }
                }
            }

            /**
             * Definition of the Options page (following default Wordpress practice).
             * 
             * @since 2.0
             * 
             * @return void
             */
            public function add_plugin_page() {

                // This page will be under "Users"
                add_users_page(
                    'WPO365 User Sync',                         // page title
                    'WPO365 User Sync',                         // menu title
                    'read',                                         // capabilities
                    'wpo365-sync-admin',                        // page slug
                    array( $this, 'wpo365_sync_admin_page' )    // callback to render page
                );
            }

            /**
             * Definition of the layout of the Options page (following default Wordpress practice).
             * 
             * @since 1.0
             * 
             * @return void
             */
            public function wpo365_sync_admin_page() {

                $sync_job_id = get_site_option( Sync_Manager::OPTION_JOB_ID, null );
                $sync_job_last_run = get_site_option( Sync_Manager::OPTION_LAST_RUN, null );
                $table_exists = Sync_Db::user_sync_table_exists();
                $table_name = Sync_Db::get_user_sync_table_name();

                $custom_domain = Options::get_global_list_var( 'custom_domain' );
                $default_domain = Options::get_global_string_var( 'default_domain' );

                ?>

                <div class="wrap">
                    <form method="post">
                        <h1>WPO365 Azure AD User Synchronization</h1>
                        <?php if( false === $table_exists ): ?>
                        <div class="notice notice-error">
                            <p>Table <strong><?php echo $table_name ?></strong> not found: Please de-activate the <strong>WPO365 Login (premium)</strong> and activate it again for the necessary table to be created.</p>
                        </div>
                        <?php endif; ?>
                        <h2>Synchronize WordPress users with Office 365 Azure AD</h2>
                        <p>
                            Please read the <a href="https://www.wpo365.com/synchronize-users-between-office-365-and-wordpress/">online documentation</a> on how to configure and use this plugin.
                        </p>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Create users</th>
                                <td>
                                    <input type="checkbox" id="wpo_create_users" name="wpo_create_users">
                                    <label style="font-size: 0.8rem; padding: 3px;">
                                        When checked the plugin will create a new WordPress user for an Office 365 Azure AD user without a matching WordPress user.
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Update users</th>
                                <td>
                                    <input type="checkbox" id="wpo_update_users" name="wpo_update_users">
                                    <label style="font-size: 0.8rem; padding: 3px;">
                                        When checked the plugin will update existing WordPress users with details taken from a matching Office 365 Azure AD user.
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Delete users</th>
                                <td>
                                    <input type="checkbox" id="wpo_delete_users" name="wpo_delete_users">
                                    <label style="font-size: 0.8rem; padding: 3px;">
                                        When checked the plugin will delete all WordPress user without a matching Office 365 Azure AD user.
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Ignore external users</th>
                                <td>
                                    <input type="checkbox" checked id="wpo_internal_users" name="wpo_internal_users">
                                    <label style="font-size: 0.8rem; padding: 3px;">
                                        When checked the plugin will only synchronize users for your custom ( <?php echo implode( ', ', $custom_domain ) ?> ) and / or default ( <?php echo $default_domain ?> ) domain.
                                        <br/><i><strong>Please note</strong> that you should only uncheck this you know what you are doing.</i>
                                    </label>
                                </td>
                            </tr>
                            <!--tr>
                                <th scope="row">Require email address</th>
                                <td>
                                    <input type="checkbox" checked id="wpo_require_email" name="wpo_require_email">
                                    <label style="font-size: 0.8rem; padding: 3px;">
                                        When checked the plugin will not synchronize Office 365 Azure AD users without an email address.
                                        <br/><i><strong>Please note</strong> that if checked the plugin may not find Office 365 Azure AD users with corresponding WordPress user but without an email address.</i>
                                    </label>
                                </td>
                            </tr-->
                        </table>
                        <p class="submit">
                            <input type="submit" name="sync_users" id="sync_users" class="button button-primary" value="Start synchronization">
                        </p>
                    
                        <h2>Synchronization Result</h2>
                        <p>
                            The last synchronization was performed on <strong><?php echo empty( $sync_job_last_run ) ? 'no sync job info available' : date( "j F Y H:i:s", intval( $sync_job_last_run ) ) . ' ' . date_default_timezone_get() ?></strong>.
                            <br><br>
                            Please use the links below to review and manage the outcome of the latest synchronization job in two separate tables.
                            <br><br>
                            If you selected <strong>Create users</strong>, the table <strong>Office 365 Azure AD users without a corresponding WordPress user</strong> will show you
                            a summary of all the Office 365 Azure AD users for those the plugin created a corresponding WordPress user. If you left this 
                            option unchecked, you can select users from the table and create them manually.
                        </p>
                        <p>
                            <strong><a href="<?php echo self::get_clean_show_table_url( 'stnu' ) . '#stnu' ?>">Office 365 Azure AD users without a corresponding WordPress user</a></strong>
                        </p>
                        <p>
                            If you selected <strong>Delete users</strong>, the table <strong>WordPress users without a corresponding Office 365 Azure AD user</strong> will show you
                            a summary of all the WordPress users the plugin deleted. If you left this option unchecked, you can select users from the table and
                            delete them manually.
                        </p>
                        <p>
                            <strong><a href="<?php echo self::get_clean_show_table_url( 'stou' ) . '#stou' ?>">WordPress users without a corresponding Office 365 Azure AD user</a></strong>
                        </p>
                        <p>
                            The table <strong>Existing WordPress users with a corresponding Office 365 Azure AD user</strong> lists existing WordPress users for which a corresponding Office 365 Azure AD user was found.
                        </p>
                        <p>
                            <strong><a href="<?php echo self::get_clean_show_table_url( 'stne' ) . '#stne' ?>">Existing WordPress users with a corresponding Office 365 Azure AD user</a></strong>
                        </p>
                        <p>
                            <input type="submit" name="action_truncate_results" id="action_truncate_results" class="button action" value="Truncate results">
                        </p>
                    </form>
                </div>

                <?php

                if( empty( $sync_job_id ) ) {

                    return;
                }

                // show table new users (O365 users without a corresponding WP user)
                if( isset( $_GET[ 'stnu' ] ) ) {

                    self::print_new_domain_users_table( $sync_job_id );
                }

                // show table new users (O365 users without a corresponding WP user)
                if( isset( $_GET[ 'stne' ] ) ) {

                    self::print_existing_domain_users_table( $sync_job_id );
                }
                    
                // show table old users (WP users without a corresponding O365 user)
                if( isset( $_GET[ 'stou' ] ) ) {

                    self::print_not_found_domain_users_table( $sync_job_id );               
                }
            }

            private static function print_new_domain_users_table( $sync_job_id ) {

                self::print_users_table(
                    'New Office 365 Azure AD users',
                    'stnu',
                    'The table below shows users found in Office 365 Azure AD for which no corresponding WordPress user was found.',
                    "WHERE sync_job_id = '$sync_job_id' AND record_type = 'new_domain_user'",
                    '<input type="submit" name="action_create_users" id="action_create_users" class="button action" value="Create Users">'
                );
            }

            private static function print_existing_domain_users_table( $sync_job_id ) {

                self::print_users_table(
                    'Existing Office 365 Azure AD users',
                    'stne',
                    'The table below shows users found in both Office 365 Azure AD and WordPress.',
                    "WHERE sync_job_id = '$sync_job_id' AND record_type = 'existing_domain_user'",
                    ''
                );
            }

            private static function print_not_found_domain_users_table( $sync_job_id ) {

                self::print_users_table(
                    'Not found Office 365 Azure AD users',
                    'stou',
                    'The table below shows users found in WordPress for those no corresponding user was found in Office 365 Azure AD.',
                    "WHERE sync_job_id = '$sync_job_id' AND record_type = 'untagged_user'",
                    '<input type="submit" name="action_delete_users" id="action_delete_users" class="button action" value="Delete Users">'
                );
            }

            private static function print_users_table( $header, $header_id, $description, $where_clause, $action_buttons ) {

                global $wpdb;

                $table_name = Sync_Db::get_user_sync_table_name();
                $page_size = defined( 'WPO_USER_SYNC_PAGE_SIZE' ) ? intval( WPO_USER_SYNC_PAGE_SIZE ) : 6;
                $page = max( isset( $_GET[ 'cpage' ] ) ? intval( $_GET[ 'cpage' ] ) : 1 , 1 );
                $offset = ( $page_size * $page ) - $page_size;

                $totals = array(
                    'all'       => $wpdb->get_var( "SELECT COUNT(upn) FROM $table_name $where_clause "),
                    'error'     => $wpdb->get_var( "SELECT COUNT(upn) FROM $table_name $where_clause AND sync_job_status = 'error'"),
                    'created'   => $wpdb->get_var( "SELECT COUNT(upn) FROM $table_name $where_clause AND sync_job_status = 'created'"),
                    'updated'   => $wpdb->get_var( "SELECT COUNT(upn) FROM $table_name $where_clause AND sync_job_status = 'updated'"),
                    'deleted'   => $wpdb->get_var( "SELECT COUNT(upn) FROM $table_name $where_clause AND sync_job_status = 'deleted'"),
                    'logged'    => $wpdb->get_var( "SELECT COUNT(upn) FROM $table_name $where_clause AND sync_job_status = 'logged'"),
                );

                $where_by_status = isset( $_GET[ 'sjs' ] ) ? ' AND sync_job_status = \'' . $_GET[ 'sjs' ] . '\' ' : '';
                $where_clause = $where_clause . $where_by_status;
                $records = $wpdb->get_results( "SELECT * FROM $table_name $where_clause LIMIT $page_size OFFSET $offset", ARRAY_A );
                $records_total = $wpdb->get_var( "SELECT COUNT(upn) FROM $table_name $where_clause ");
                
                echo '<div class="wrap">';
                echo "<h2 style=\"font-size: 1.3em; font-weight: 600;\" id=\"$header_id\">[Table] $header</h2>";
                echo "<p>$description</p>";
                    
                if( $totals[ 'all' ] > 0 ):
                    
                    echo '<ul class="subsubsub">';
                    echo '<li><a href="' . self::get_clean_show_table_url( $header_id ) . "#$header_id" . '" class="current" aria-current="page">All <span class="count">(' . $totals[ 'all' ] . ')</span></a> |</li>';
                    if( $header_id == 'stou' ) {
                        echo '<li><a href="' . self::get_sjs_url( 'deleted' ). "#$header_id" . '">Deleted <span class="count">(' . $totals[ 'deleted' ] . ')</span></a> |</li>';
                    }
                    if( $header_id == 'stnu' ) {
                        echo '<li><a href="' . self::get_sjs_url( 'created' ). "#$header_id" . '">Created <span class="count">(' . $totals[ 'created' ] . ')</span></a> |</li>';
                    }
                    if( $header_id == 'stne' ) {
                        echo '<li><a href="' . self::get_sjs_url( 'updated' ). "#$header_id" . '">Created <span class="count">(' . $totals[ 'created' ] . ')</span></a> |</li>';
                    }
                    echo '<li><a href="' . self::get_sjs_url( 'error' ). "#$header_id" . '">Error <span class="count">(' . $totals[ 'error' ] . ')</span></a> |</li>';
                    echo '<li><a href="' . self::get_sjs_url( 'logged' ). "#$header_id" . '">Logged <span class="count">(' . $totals[ 'logged' ] . ')</span></a></li>';
                    echo '</ul>';

                    echo '<form method="post" name="users_table" id="users_table">';
                    echo '<table class="wp-list-table widefat fixed striped" style="width: 90%">';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<td class="manage-column column-cb check-column"><input id="cb-select-all-1" type="checkbox"></td>';
                    echo '<th scope="col" id="title" class="manage-column">WordPress ID</th>';
                    //echo '<th scope="col" id="title" class="manage-column">First Name</th>';
                    //echo '<th scope="col" id="title" class="manage-column">Last Name</th>';
                    echo '<th scope="col" id="title" class="manage-column">Full Name</th>';
                    echo '<th scope="col" id="title" class="manage-column">User Principal Name</th>';
                    echo '<th scope="col" id="title" class="manage-column">Email</th>';
                    echo '<th scope="col" id="title" class="manage-column">Status</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';

                    foreach( $records as $record ) {

                        $checkable = $record[ 'sync_job_status' ] == 'logged' ? true : false;
                        $check_value = $record[ 'record_type' ] == 'new_domain_user' ? $record[ 'upn' ] : $record[ 'wp_id' ];

                        echo '<tr>';
                        echo '<th scope="row" class="check-column">';
                        echo $checkable ? '<input id="cb-select-1" type="checkbox" name="post[]" value="' . $check_value . '">' : '';
                        echo '</th>';
                        echo '<td>' . $record[ 'wp_id' ] . '</td>';
                        //echo '<td>' . $record[ 'first_name' ] . '</td>';
                        //echo '<td>' . $record[ 'last_name' ] . '</td>';
                        echo '<td>' . $record[ 'full_name' ] . '</td>';
                        echo '<td>' . $record[ 'upn' ] . '</td>';
                        echo '<td>' . $record[ 'email' ] . '</td>';
                        echo '<td>' . $record[ 'sync_job_status' ] . '</td></tr>';

                    }

                    echo '</tbody>';
                    echo '</table>';

                    echo '<div class="tablenav bottom" style="width: 90%;"><div class="alignleft actions">';
                    echo $action_buttons;
                    echo '</div><div class="alignright actions">';

                    echo self::get_paginate_links( $page_size, $records_total, $header_id );
                    
                    echo '</div></div></form>';

                else:

                    echo '<i><span>No results.</span></i>';

                endif;

                echo '<p><strong>Please note</strong> that you can change the default page size of the tables below by <a href="https://codex.wordpress.org/Editing%20wp-config.php">adding the following line to your wp-config.php</a> file <strong>define( \'WPO_USER_SYNC_PAGE_SIZE\', 10);</strong> after the line that reads <strong>/* That\'s all, stop editing! Happy blogging. */</strong></p>';
                echo '</div>';
            }

            private static function get_paginate_links( $page_size, $total, $header_id ) {

                return paginate_links( 
                    array(
                        'base'          => add_query_arg( 'cpage', '%#%' ),
                        'format'        => '',
                        'current'       => max( isset( $_GET[ 'cpage' ] ) ? intval( $_GET[ 'cpage' ] ) : 1 , 1 ),
                        'total'         => ceil( intval( $total ) / $page_size ),
                        'add_fragment'  => "#$header_id",
                    ) 
                );
            }

            private static function get_clean_show_table_url( $query_arg_to_add ) {

                $url = remove_query_arg( 'stnu' );
                $url = remove_query_arg( 'stne', $url );
                $url = remove_query_arg( 'stou', $url );
                $url = remove_query_arg( 'cpage', $url );
                $url = remove_query_arg( 'sjs', $url );
                $url = add_query_arg( $query_arg_to_add, 1, $url );
                return $url;
            }

            private static function get_sjs_url( $status ) {

                $url = remove_query_arg( 'sjs' );
                $url = remove_query_arg( 'cpage', $url );
                return add_query_arg( 'sjs', $status, $url );
            }
        }
    }