<?php

@session_start();
require_once(dirname(__FILE__) . '/config.php');
$con = mysqli_connect($hostname, $username, $password, $database);
$conn = $con;
$conn->set_charset('utf8mb4');


/**
 * databse class
 */
class Database
{
    public $connection;

    public static $instance;

    public function __construct()
    {
        require(dirname(__FILE__) . '/config.php');
        $this->connection = new  mysqli($hostname, $username, $password, $database);
        $this->connection->set_charset('utf8mb4');
        // $this->connection->set_charset('utf8');
        self::$instance = $this;
    }


    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getNumberOfThisMonthsReviews()
    {
        $currentMonth = date("Y-m");
        $sql = "SELECT COUNT(*) as numberOfReviews FROM custom_reviews WHERE date LIKE '" . $currentMonth . "%' AND loom_url != ''";

        $numberOfReviews = null;

        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $numberOfReviews = $row;
            }
            $result->free();
        }

        return $numberOfReviews;
    }

    public function getUserProfile($id)
    {
        if ($this->checkPlanExpired($id)) {
            $sql = "SELECT u.`status`,
        `access_token`,
        `app_id`,
        `app_secret`,
        `google_key`,
        `yelp_api_key`,
        `short_io_api_key`,
        u.`name`,
        u.`email`,
        u.`status`,
        u.`address`,
        1 advanced_settings,
        u.`id`,
        u.`phone`,
        p.`no_of_campaigns`,
        p.`fb_reviews_cnt`,
        p.`google_reviews_cnt`,
        p.`yelp_reviews_cnt`,
        0 use_default_credentials,
        u.`pwd`,
        (select p.`title` from `plans` p where p.`id`=u.`plan`) as plan_name,
        (select p.`id` from `plans` p where p.`id`=u.`plan`) as plan_id FROM `user` u, `plans` p  where p.`id`=(select `param_value` from `settings` where `param_name`='default_plan_id' LIMIT 1) and  u.`id` = '" . $id . "' LIMIT 1";
        } else {
            $sql = "SELECT u.`status`,
      `access_token`,
      `app_id`,
      `app_secret`,
      `google_key`,
      `yelp_api_key`,
      `short_io_api_key`,
      u.`name`,
      u.`email`,
      u.`status`,
      u.`address`,
      u.`advanced_settings`,
      u.`id`,
      u.`phone`,
      u.`no_of_campaigns`,
      u.`fb_reviews_cnt`,
      u.`google_reviews_cnt`,
      u.`yelp_reviews_cnt`,
      u.`use_default_credentials`,
      u.`pwd`,
       (select p.`title` from `plans` p where p.`id`=u.`plan`) as plan_name,
        (select p.`id` from `plans` p where p.`id`=u.`plan`) as plan_id FROM `user` u where u.`id` = '" . $id . "' LIMIT 1";
        }
        $user = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $user = $row;
            }
            $result->free();
        }


        return $user;
    }

    public function checkPlanExpired($user_id)
    {
        $current_date = gmdate('Y-m-d H:i:s');
        $last_plan_date = date($this->getLastUserPlan($user_id)['date_stop']);

        return ($last_plan_date > $current_date) ? false : true;
    }


    public function getUser($id)
    {
        if ($this->checkPlanExpired($id)) {
            $sql = "SELECT u.`status`,
        `access_token`,
        case when trim(COALESCE(`app_id`,''))=''   then
        (select `param_value` from `settings` where `param_name`='default_fb_app_id' LIMIT 1 )
        else `app_id` end as `app_id`,
        case when trim(COALESCE(`app_secret`,''))='' then
        (select `param_value` from `settings` where `param_name`='default_fb_app_secret' LIMIT 1 )
        else `app_secret` end as `app_secret`,
        `google_key`,
        `yelp_api_key`,
        u.`name`,
        u.`email`,
        u.`status`,
        u.`address`,
        1 advanced_settings,
        u.`id`,
        u.`phone`,
        p.`no_of_campaigns`,
        p.`fb_reviews_cnt`,
        p.`google_reviews_cnt`,
        p.`yelp_reviews_cnt`,
        0 use_default_credentials,
        u.`pwd`,
        (select p.`title` from `plans` p where p.`id`=u.`plan`) as plan_name FROM `user` u, `plans` p  where p.`id`=(select `param_value` from `settings` where `param_name`='default_plan_id' LIMIT 1) and  u.`id` = '" . $id . "' LIMIT 1";
        } else {
            $sql = "SELECT u.`status`,
      `access_token`,
      case when trim(COALESCE(`app_id`,''))=''   then
      (select `param_value` from `settings` where `param_name`='default_fb_app_id' LIMIT 1 )
      else `app_id` end as `app_id`,
      case when trim(COALESCE(`app_secret`,''))=''  then
      (select `param_value` from `settings` where `param_name`='default_fb_app_secret' LIMIT 1 )
      else `app_secret` end as `app_secret`,
      case when trim(COALESCE(`google_key`,''))='' and coalesce(`use_default_credentials`,0)=1  then
      (select `param_value` from `settings` where `param_name`='default_google_key' LIMIT 1 )
      else `google_key` end as `google_key`,
      case when trim(COALESCE(`yelp_api_key`,''))='' and coalesce(`use_default_credentials`,0)=1 then
      (select `param_value` from `settings` where `param_name`='default_yelp_api_key' LIMIT 1 )
      else `yelp_api_key` end as `yelp_api_key`,
      u.`name`,
      u.`email`,
      u.`status`,
      u.`address`,
      u.`advanced_settings`,
      u.`id`,
      u.`phone`,
      u.`no_of_campaigns`,
      u.`fb_reviews_cnt`,
      u.`google_reviews_cnt`,
      u.`yelp_reviews_cnt`,
      u.`use_default_credentials`,
      u.`pwd`,
       (select p.`title` from `plans` p where p.`id`=u.`plan`) as plan_name FROM `user` u where u.`id` = '" . $id . "' LIMIT 1";
        }
        $user = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $user = $row;
            }
            $result->free();
        }


        return $user;
    }

    public function getUserByEmail($email)
    {
        $sql = "SELECT * FROM `user` where `email`='" . $email . "' LIMIT 1";
        $user = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $user = $row;
            }
            $result->free();
        }


        return $user;
    }

    public function getUserCampaignsByUserId($id)
    {
        $sql = 'SELECT * from `campaigns` where `user_id` =' . (int)$id;

        $campaings = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $campaings[$row['id']] = $row;
            }
            $result->free();
        }


        return $campaings;
    }

    public function getCampaignByUserIdCampaignId($user_id, $campaing_id)
    {
        if ($this->checkPlanExpired($user_id)) {

            $sql = 'SELECT
    (select GROUP_CONCAT(t.`name`) from `tags` t, `campaigns_tags` ct
                    where ct.`campaigns_id`=c.`id` and t.`id`=ct.`tags_id`) tags,

                         c.* ,
                         p.`fb_reviews_cnt`,
                             p.`google_reviews_cnt`,
                             p.`yelp_reviews_cnt`

                         from `campaigns` c,  `plans` p
                         where p.`id`=(select `param_value` from `settings` where `param_name`="default_plan_id" LIMIT 1)
    and c.`user_id` =' . (int)$user_id . ' and c.`id`=' . (int)$campaing_id . ' LIMIT 1';
        } else {

            $sql = 'SELECT
      (select GROUP_CONCAT(t.`name`) from `tags` t, `campaigns_tags` ct
                      where ct.`campaigns_id`=c.`id` and t.`id`=ct.`tags_id`) tags,
      c.* from `campaigns` c where c.`user_id` =' . (int)$user_id . ' and c.`id`=' . (int)$campaing_id . ' LIMIT 1';
        }


        $return = null;
        $is_expired = $this->checkPlanExpired($id);
        if ($is_expired) {
        }
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {


                $return = $row;
            }
            $result->free();
        }


        return $return;
    }


    public function getCampaignByCampaignId($campaing_id)
    {
        $sql = 'SELECT * from `campaigns` where `id`=' . (int)$campaing_id . ' LIMIT 1';

        $return = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $return = $row;
            }
            $result->free();
        }


        return $return;
    }

    public function getUserCampaignsCount($id)
    {
        $sql = 'SELECT count(`id`) from `campaigns` where `user_id` =' . (int)$id;
        $return = 0;
        if ($result = $this->connection->query($sql)) {
            $return = $result->fetch_row()[0];
        }
        return $return;
    }

    public function getUserAllCampaignFbPages($user_id)
    {
        $sql = "SELECT `fb_page` FROM `campaigns` where `user_id` = " . (int)$user_id;
        $return = null;

        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $return[] = $row['fb_page'];
            }
            $result->free();
        }


        return $return;
    }

    public function getLastReviewsUpdateDatesByCampaingId($campaing_id)
    {
        $sql = "SELECT r.`google_last_update`, r.`fb_last_update`, r.`yelp_last_update`  FROM `reviews_cache` r where r.`id_campaign` = " . (int)$campaing_id . " LIMIT 1";

        $return = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $return = $row;
            }
            $result->free();
        }

        return $return;
    }

    public function getLastReviewsByCampaingId($campaing_id, $type = 'google_reviews')
    {
        $sql = "SELECT r.`" . trim($type) . "` FROM `reviews_cache` r where r.`id_campaign` = " . (int)$campaing_id . " LIMIT 1";

        $return = null;
        if ($result = $this->connection->query($sql)) {
            $return[$type] = $result->fetch_row()[0];
            $result->free();
        }

        return $return;
    }

    public function updateGoogleReviewCacheByCampaignId($campaing_id, $cache)
    {
        $current_date = gmdate('Y-m-d H:i:s');
        $sql = "INSERT INTO `reviews_cache`(`id_campaign`,`google_reviews`,`google_last_update`) VALUES( ?, ?, ?) ON DUPLICATE KEY UPDATE `google_reviews` = ?, `google_last_update`=?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            'dssss',
            $campaing_id,
            $cache,
            $current_date,
            $cache,
            $current_date
        );
        $stmt->execute();
        return true;
    }

    public function updateFacebookReviewCacheByCampaignId($campaing_id, $cache)
    {
        $current_date = gmdate('Y-m-d H:i:s');
        $sql = "INSERT INTO `reviews_cache`(`id_campaign`,`fb_reviews`,`fb_last_update`) VALUES( ?, ?, ?) ON DUPLICATE KEY UPDATE `fb_reviews` = ?, `fb_last_update`=?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            'dssss',
            $campaing_id,
            $cache,
            $current_date,
            $cache,
            $current_date
        );
        $stmt->execute();
        return true;
    }


    public function updateYelpReviewCacheByCampaignId($campaing_id, $cache)
    {
        $current_date = gmdate('Y-m-d H:i:s');
        $sql = "INSERT INTO `reviews_cache`(`id_campaign`,`yelp_reviews`,`yelp_last_update`) VALUES( ?, ?, ?) ON DUPLICATE KEY UPDATE `yelp_reviews` = ?, `yelp_last_update`=?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            'dssss',
            $campaing_id,
            $cache,
            $current_date,
            $cache,
            $current_date
        );
        $stmt->execute();
        return true;
    }


    public function updateUserVisitsByUserId($user_id)
    {
        $current_date = gmdate('Y-m-d H:i:s');
        $sql = 'insert INTO `user_visits`(`user_id`, `data_day`, `visit_qty`) VALUES (' . (int)$user_id . ',\'' . $current_date . '\',`visit_qty`+1) on DUPLICATE key UPDATE `visit_qty`=`visit_qty`+1';

        $this->connection->query($sql);
        return true;
    }

    public function updateGeneralSettings($values)
    {
        $where = "";
        $sql = "UPDATE `settings` set `param_value` =
    CASE `param_name` ";
        foreach ($values as $key => $value) {
            $sql .= " WHEN '" . $key . "' THEN '" . $value . "'";
            $where .= "'" . $key . "',";
        }
        $sql .= ' ELSE `param_value` END';
        $this->connection->query($sql);
        return true;
    }

    public function getGeneralSettings($param_name = "")
    {
        $sql = "SELECT * FROM `settings`" . (($param_name != "" ? " WHERE `param_name`='" . $param_name . "'" : ""));

        $return = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $return[$row['param_name']] = $row['param_value'];
            }
            $result->free();
        }

        return $return;
    }


    public function initGeneralSettings()
    {

        $sql = "INSERT IGNORE INTO `settings` (`param_name`) VALUES
                ('version'),
                ('business_email'),
                ('privacy_policy_url'),
                ('terms_conditions_url'),
                ('default_fb_app_id'),
                ('default_fb_app_secret'),
                ('default_fb_verify_app_id'),
                ('default_fb_verify_app_secret'),
                ('default_yelp_api_key'),
                ('default_google_key'),
                ('default_fb_access_token'),
                ('default_plan_id'),
                ('default_google_client_id'),
                ('default_google_client_secret'),
                ('proxy_settings')
        ";
        $this->connection->query($sql);
        return true;
    }


    public function getAllPlans($only_active = false)
    {
        $sql = "SELECT * FROM `plans`" . (($only_active ? " WHERE `active`=1" : ""));
        $return = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $return[] = $row;
            }
            $result->free();
        }

        return $return;
    }


    public function getProfilePlans($id_user)
    {
        $sql = "SELECT p.* from `plans` p where p.`id`=" . $this->getGeneralSettings('default_plan_id')['default_plan_id'] . " or exists(select * from `user_plans` up where up.`plan_id`=p.`id` and up.`user_id`=" . $id_user . "
            and up.`date_stop` >= '" . gmdate('Y-m-d H:i:s') . "' limit 1 )";
        $return = null;

        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $return[] = $row;
            }
            $result->free();
        }

        return $return;
    }


    public function getPlanById($plan_id)
    {
        $sql = "SELECT * FROM `plans` where `id` = " . (int)$plan_id;

        $return = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $return = $row;
            }
            $result->free();
        }


        return $return;
    }


    public function getPlanByPlugNPaidProductId($plugnpaid_product_id)
    {
        $sql = "SELECT * FROM `plans` where `plugnpaid_product_id` = '" . $plugnpaid_product_id . "'";

        $return = null;

        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $return = $row;
            }
            $result->free();
        }


        return $return;
    }


    public function updatePlan($data)
    {
        $sql = "UPDATE `plans` set `title` = ? ,`description` = ?, `amount` = ?, `no_of_campaigns` = ?,
          `features_list` = ?,
          `pricing_frequency` = ? ,
          `pricing_button` = ?,
          `pricing_column_classes` = ?,
          `pricing_ribbon` = ?,
          `fb_reviews_cnt`= ?,
          `google_reviews_cnt`= ?,
          `yelp_reviews_cnt`= ?,
          `custom_reviews_cnt`= ?,
          `use_default_credentials` = ?,
          `plugnpaid_product_id` = ?,
          `plugnpaid_plug_link` = ?,
          `active` = ?
          where `id` = ?";


        $use_default_credentials = ((isset($data['use_default_credentials']) && $data['use_default_credentials'] = "on") ? 1 : 0);
        $active = ((isset($data['active']) && $data['active'] = "on") ? 1 : 0);

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            'sssssssssssssdssdd',
            $data['title'],
            $data['description'],
            $data['amount'],
            $data['no_of_campaigns'],
            $data['features_list'],
            $data['pricing_frequency'],
            $data['pricing_button'],
            $data['pricing_column_classes'],
            $data['pricing_ribbon'],
            $data['fb_reviews_cnt'],
            $data['google_reviews_cnt'],
            $data['yelp_reviews_cnt'],
            $data['custom_reviews_cnt'],
            $use_default_credentials,
            $data['plugnpaid_product_id'],
            $data['plugnpaid_plug_link'],
            $active,
            $data['id']
        );

        //
        // $stmt->execute();
        // return true;

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function updateUser($data)
    {
        $sql = "UPDATE `user` SET
          `name` = '" . $data['name'] . "' ,
          `email` = '" . $data['email'] . "',
          `address` = '" . @$data['address'] . "',
          `phone` = '" . $data['phone'] . "',
          `status` = '" . $data['status'] . "'

          WHERE `id` = '" . $data['id'] . "'";
        if ($this->connection->query($sql)) {
            return true;
        } else {
            return false;
        }
    }

    public function getCurrentUserPlan($user_id)
    {
        $current_date = gmdate('Y-m-d H:i:s');
        $sql = "SELECT up.*, (SELECT p.`title` FROM `plans` p where p.`id` = up.`plan_id`) plan_name FROM `user_plans` up  where up.`user_id` = " . (int)$user_id . " and up.date_stop >='" . $current_date . "' ORDER BY up.`id` desc LIMIT 1";


        $return = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $return = $row;
            }
            $result->free();
        }


        if ($return == null) {
            $def_plan = $this->getPlanById($this->getGeneralSettings('default_plan_id')['default_plan_id']);
            $return['plan_id'] = $def_plan['id'];
            $return['date_stop'] = gmdate('Y-m-d H:i:s', strtotime("+100 years"));
            $return['plan_name'] = $def_plan['title'];
        }

        return $return;
    }

    public function isUserCurrentPlanDefault($user_id)
    {
        $user_plan = $this->getCurrentUserPlan($user_id);
        $def_plan = $this->getPlanById($this->getGeneralSettings('default_plan_id')['default_plan_id']);

        return $user_plan['plan_id'] == $def_plan['id'];
    }

    public function getLastUserPlan($user_id)
    {
        $sql = "SELECT up.*, (SELECT p.`title` FROM `plans` p where p.`id` = up.`plan_id`) plan_name FROM `user_plans` up  where up.`user_id` = " . (int)$user_id . " ORDER BY up.`id` desc LIMIT 1";

        $return = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $return = $row;
            }
            $result->free();
        }


        if ($return == null) {
            $def_plan = $this->getPlanById($this->getGeneralSettings('default_plan_id')['default_plan_id']);
            $return['plan_id'] = $def_plan['id'];
            $return['date_stop'] = gmdate('Y-m-d H:i:s', strtotime("+100 years"));
            $return['plan_name'] = $def_plan['title'];
        }

        return $return;
    }

    public function setActivePlan($data)
    {
        $sql = "SELECT up.`id`, up.`plan_id` FROM `user_plans` up  where up.`user_id` = " . (int)$data['user_id'] . " ORDER BY up.`id` desc LIMIT 1";

        $current_plan_id = null;
        $user_plan_id = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $current_plan_id = $row['plan_id'];
                $user_plan_id = $row['id'];
            }
            $result->free();
        }


        if (array_key_exists('plan_id', $data) && (int)$data['plan_id'] != (int)$current_plan_id) {
            $new_plan = $this->getPlanById((int)$data['plan_id']);

            $user = $this->getUser($data['user_id']);
            $sql = "UPDATE `user` SET
            `no_of_campaigns` = ?,
            `fb_reviews_cnt` = ?,
            `google_reviews_cnt` = ?,
            `yelp_reviews_cnt` = ?,
            `plan` = ?,
            `advanced_settings` = ?,
            `use_default_credentials` = ?
            WHERE `id` = ?";
            $advanced_settings = ($new_plan['use_default_credentials'] == 0 ? 1 : (int)$user['advanced_settings']);
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param(
                'dddddddd',
                $new_plan['no_of_campaigns'],
                $new_plan['fb_reviews_cnt'],
                $new_plan['google_reviews_cnt'],
                $new_plan['yelp_reviews_cnt'],
                $new_plan['id'],
                $advanced_settings,
                $new_plan['use_default_credentials'],
                $data['user_id']
            );
            $stmt->execute();


            $sql = 'SELECT `id`,`plan_id`,`user_id`, `date_start`,`date_start`,`date_stop`,`creation_date` from `user_plans` where `user_id` =' . (int)$data['user_id'] . ' and `plan_id`=' . $data['plan_id'] . ' order by `date_stop` desc limit 1 ';

            $res = null;
            if ($result = $this->connection->query($sql)) {
                while ($row = $result->fetch_assoc()) {
                    $res = $row;
                }
                $result->free();
            }
            if ($res != null) {
                $this->connection->query('DELETE FROM `user_plans` WHERE `id`=' . (int)$res['id']);
            } else {
                switch (strtolower($new_plan['pricing_frequency'])) {
                    case 'always':
                        $next_date = gmdate('Y-m-d H:i:s', strtotime("+100 years"));
                        break;
                    case 'year':
                        $next_date = gmdate('Y-m-d H:i:s', strtotime("+1 years"));
                        break;
                    default:
                        $next_date = gmdate('Y-m-d H:i:s', strtotime("+1 months"));
                        break;
                }

                $res['plan_id'] = $data['plan_id'];
                $res['user_id'] = $data['user_id'];
                $res['date_start'] = gmdate('Y-m-d H:i:s');
                $res['date_stop'] = $next_date;
                $res['creation_date'] = gmdate('Y-m-d H:i:s');
            }

            $sql = "INSERT INTO `user_plans`(`plan_id`,`user_id`,`date_start`,`date_stop`,`creation_date`) VALUES( ?, ?, ?, ?, ?) ";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param(
                'ddsss',
                $res['plan_id'],
                $res['user_id'],
                $res['date_start'],
                $res['date_stop'],
                $res['creation_date']
            );
            $stmt->execute();
        }

        return true;
    }

    public function updateUserPlan($data)
    {
        if (!isset($data['cancellation_link'])) {
            $data['cancellation_link'] = "";
        }


        $default_plan_id = $this->getGeneralSettings('default_plan_id')['default_plan_id'];

        $def_plan = $this->getPlanById($default_plan_id);

        // $sql = "SELECT up.`id`, up.`plan_id` FROM `user_plans` up  where up.`user_id` = " . (int)$data['id'] . " ORDER BY up.`id` desc LIMIT 1";
        //
        // $current_plan_id = null;
        // $user_plan_id = null;
        // if ($result = $this->connection->query($sql)) {
        //     while ($row = $result->fetch_assoc()) {
        //         $current_plan_id = $row['plan_id'];
        //         $user_plan_id = $row['id'];
        //     }
        //     $result->free();
        // }

        $current_plan_id = $this->getCurrentUserPlan((int)$data['id'])['plan_id'];





        if (array_key_exists('plan', $data) && (int)$data['plan'] != (int)$current_plan_id) {


            $new_plan = $this->getPlanById((int)$data['plan']);
            $user = $this->getUser($data['id']);



            switch (strtolower($new_plan['pricing_frequency'])) {
                case 'always':
                    $next_date = gmdate('Y-m-d H:i:s', strtotime("+100 years"));
                    break;
                case 'year':
                    $next_date = gmdate('Y-m-d H:i:s', strtotime("+1 years"));
                    break;
                default:
                    $next_date = gmdate('Y-m-d H:i:s', strtotime("+1 months"));
                    break;
            }


            $current_date = gmdate('Y-m-d H:i:s');

            if ($data['plan'] == $default_plan_id) {
                $sql = 'SELECT `id` from `user_plans` where `user_id` =' . (int)$data['id'] . ' and `plan_id`=' . $data['plan'] . ' order by `id` desc';
                $res = 0;
                if ($result = $this->connection->query($sql)) {
                    while ($row = $result->fetch_assoc()) {
                        $res = $row['id'];
                    }
                    $result->free();
                }
                if ($res != 0) {
                    $sql = "UPDATE `user_plans` p join (select `id` +1 as `id`
                                            from `user_plans` order by `id` desc
                                            limit 1) p2
                set p.`date_stop`='" . $next_date . "', p.`id`=p2.`id` WHERE p.`id`=" . (int)$res;

                    $this->connection->query($sql);
                } else {
                    $sql = "INSERT INTO `user_plans`(`plan_id`,`user_id`,`date_start`,`date_stop`,`creation_date`,`plugnpaid_cancellation_link`) VALUES( ?, ?, ?, ?, ?,?) ";
                    $stmt = $this->connection->prepare($sql);
                    $stmt->bind_param(
                        'ddssss',
                        $data['plan'],
                        $data['id'],
                        $current_date,
                        $next_date,
                        $current_date,
                        $data['cancellation_link']
                    );

                    $stmt->execute();
                }
            } else {

                $sql = "INSERT INTO `user_plans`(`plan_id`,`user_id`,`date_start`,`date_stop`,`creation_date`,`plugnpaid_cancellation_link`) VALUES( ?, ?, ?, ?, ?, ?) ";
                $stmt = $this->connection->prepare($sql);
                $stmt->bind_param(
                    'ddssss',
                    $data['plan'],
                    $data['id'],
                    $current_date,
                    $next_date,
                    $current_date,
                    $data['cancellation_link']
                );
                $stmt->execute();
            }
            $sql = "UPDATE `user` SET
            `no_of_campaigns` = ?,
            `fb_reviews_cnt` = ?,
            `google_reviews_cnt` = ?,
            `yelp_reviews_cnt` = ?,
            `plan` = ?,
            `advanced_settings` = ?,
            `use_default_credentials` = ?
            WHERE `id` = ?";

            $advanced_settings = ($new_plan['use_default_credentials'] == 0 ? 1 : (int)$user['advanced_settings']);
            $stmt = $this->connection->prepare($sql);


            $stmt->bind_param(
                'dddddddd',
                $new_plan['no_of_campaigns'],
                $new_plan['fb_reviews_cnt'],
                $new_plan['google_reviews_cnt'],
                $new_plan['yelp_reviews_cnt'],
                $new_plan['id'],
                $advanced_settings,
                $new_plan['use_default_credentials'],
                $data['id']
            );
            $stmt->execute();
        }
        return true;
    }


    public function updateUserPlanParams($data)
    {
        $new_plan = $this->getPlanById((int)$data['plan_id']);


        $user = $this->getUser($data['user_id']);
        $sql = "UPDATE `user` SET
              `no_of_campaigns` = ?,
              `fb_reviews_cnt` = ?,
              `google_reviews_cnt` = ?,
              `yelp_reviews_cnt` = ?,
              `plan` = ?,
              `advanced_settings` = ?,
              `use_default_credentials` = ?
              WHERE `id` = ?";
        $advanced_settings = ($new_plan['use_default_credentials'] == 0 ? 1 : (int)$user['advanced_settings']);
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            'dddddddd',
            $new_plan['no_of_campaigns'],
            $new_plan['fb_reviews_cnt'],
            $new_plan['google_reviews_cnt'],
            $new_plan['yelp_reviews_cnt'],
            $new_plan['id'],
            $advanced_settings,
            $new_plan['use_default_credentials'],
            $data['user_id']
        );
        $stmt->execute();
    }

    public function getUserPlans($user_id)
    {
        $sql = "SELECT p.`title`, up.* FROM `user_plans` up, `plans` p WHERE  p.`id`=up.`plan_id` AND  up.`user_id`=" . (int)$user_id . " order by `up`.id DESC";
        $return = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $return[] = $row;
            }
            $result->free();
        }

        return $return;
    }

    public function getUsers()
    {
        $sql = "SELECT u.`id`,u.`email`,u.`phone`,u.`type`,u.`status`, u.`name`,
          COALESCE((select sum(v.`visit_qty`) from `user_visits` v where   v.`user_id` = u.`id` and v.`data_day` >= (CURRENT_DATE - INTERVAL 30 DAY)),0) visit_qty,
          (SELECT p.`title` FROM `plans` p where p.`id` = u.`plan`)  plan_name,
          `date_add`


          FROM `user` u where 1";

        $return = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $row['user_plans_name'] = $this->getCurrentUserPlan($row['id'])['plan_name'];
                $return[] = $row;
            }
            $result->free();
        }

        return $return;
    }


    public function updateUserPlanPeriod($data)
    {
        $sql = "UPDATE `user_plans` SET
          `date_start` = '" . $data['date_start'] . "' ,
          `date_stop` = '" . $data['date_stop'] . "'

          WHERE `id` = '" . $data['id'] . "'";
        if ($this->connection->query($sql)) {
            return true;
        } else {
            return false;
        }
    }

    public function prolongCurrentUserPlanPeriod($user_id, $plan_id, $plugnpaid_cancellation_link = "")
    {

        $sql = "SELECT p.pricing_frequency, up.* FROM `user_plans` up, `plans` p  where p.`id`=up.`plan_id` and  up.`user_id` = " . (int)$user_id . " and up.`plan_id`=" . (int)$plan_id . " ORDER BY up.`id` desc LIMIT 1";

        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $user_plan = $row;
            }
            $result->free();
        }

        $current_date = gmdate('Y-m-d H:i:s');
        $last_plan_date = date($user_plan['date_stop']);

        switch (strtolower($user_plan['pricing_frequency'])) {
            case 'always':
                $next_date = gmdate('Y-m-d H:i:s', strtotime("+100 years"));
                break;
            case 'year':
                $next_date = gmdate('Y-m-d H:i:s', strtotime("+1 years"));
                break;
            default:
                $next_date = gmdate('Y-m-d H:i:s', strtotime("+1 months"));
                break;
        }


        if ($last_plan_date > $current_date) {
            $days = round((strtotime($last_plan_date) - strtotime($current_date)) / (60 * 60 * 24));
            $next_date = date('Y-m-d H:i:s', strtotime($next_date . ' +' . +$days . ' days'));

            $sql = "UPDATE `user_plans` set `date_stop`='" . gmdate('Y-m-d H:i:s') . "' WHERE `id`=" . (int)$user_plan['id'];
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
        }
        $sql = "INSERT INTO `user_plans`(`plan_id`,`user_id`,`date_start`,`date_stop`,`creation_date`,`plugnpaid_cancellation_link`) VALUES( ?, ?, ?, ?, ?, ?) ";
        $stmt2 = $this->connection->prepare($sql);


        if($plugnpaid_cancellation_link==null) {
          $plugnpaid_cancellation_link = "";

        }

        $stmt2->bind_param(
            'ddssss',
            $user_plan['plan_id'],
            $user_plan['user_id'],
            $current_date,
            $next_date,
            $current_date,
            $plugnpaid_cancellation_link
        );



        $stmt2->execute();

        return true;


        // if (array_key_exists('plan_id',$data) && (int)$data['plan_id'] != (int)$current_plan_id) {
        //
        //   $new_plan = $this->getPlanById((int)$data['plan_id']);
        //
        //   $user = $this->getUser($data['user_id']);
        //   $sql = "UPDATE `user` SET
        //           `no_of_campaigns` = ?,
        //           `fb_reviews_cnt` = ?,
        //           `google_reviews_cnt` = ?,
        //           `yelp_reviews_cnt` = ?,
        //           `plan` = ?,
        //           `advanced_settings` = ?,
        //           `use_default_credentials` = ?
        //           WHERE `id` = ?";
        //   $advanced_settings = ($new_plan['use_default_credentials']==0 ? 1 : (int)$user['advanced_settings']);
        //   $stmt = $this->connection->prepare($sql);
        //   $stmt->bind_param(
        //     'dddddddd',
        //     $new_plan['no_of_campaigns'],
        //     $new_plan['fb_reviews_cnt'],
        //     $new_plan['google_reviews_cnt'],
        //     $new_plan['yelp_reviews_cnt'],
        //     $new_plan['id'],
        //     $advanced_settings,
        //     $new_plan['use_default_credentials'],
        //     $data['user_id']
        //
        //   );
        //   $stmt->execute();
    }

    public function removeCancellationLinkFromUsesPlans($id)
    {
        $sql = "UPDATE `user_plans` set `plugnpaid_cancellation_link`='' WHERE `id`=" . (int)$id;
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return true;
    }

    public function getCustomReviewByIdUserId($id, $user_id)
    {
        $sql = "SELECT (select GROUP_CONCAT(t.`name`) from `tags` t, `custom_reviews_tags` crt
                  where crt.`custom_reviews_id`=cr.`id` and t.`id`=crt.`tags_id`) tags,
                  cr.*,(select `title` from `capture_reviews` where id=cr.`capture_reviews_id`) capture_reviews_title  FROM `custom_reviews` cr where cr.`id` = " . (int)$id . " and cr.`user_id` = " . $user_id;
        $this->connection->query($sql);


        $return = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $return = $row;
            }
            $result->free();
        }


        return $return;
    }


    public function getCustomReviewsByCaptureIdUserId($id, $user_id, $add_fieldnames = true)
    {
        $sql = "SELECT
        (select `title` from `capture_reviews` where id=cr.`capture_reviews_id`) capture_reviews_title,

                          cr.`date`,
                          cr.`name`,
                          cr.`email`,
                          cr.`rating`,
                          cr.`review`,

                          cr.`photo`,
                          cr.`icon`,
                          (select GROUP_CONCAT(t.`name`) from `tags` t, `custom_reviews_tags` crt
                                            where crt.`custom_reviews_id`=cr.`id` and t.`id`=crt.`tags_id`) tags,
                          cr.`feedback_text`


         FROM `custom_reviews` cr where cr.`capture_reviews_id` = " . (int)$id . " and cr.`user_id` = " . $user_id;
        $this->connection->query($sql);


        $return = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $return[] = $row;
            }
            $result->free();
        }


        return $return;
    }

    public function getTagsByUserId($user_id)
    {
        $sql = "SELECT * FROM `tags` t where t.`user_id`=" . (int)$user_id;

        $return = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $return[] = $row;
            }
            $result->free();
        }

        return $return;
    }


    public function getCustomReviewsByUserIdCampaignId($user_id, $campaing_id)
    {

        //
        $sql = "SELECT cr.* FROM `custom_reviews` cr
          WHERE cr.`user_id` = " . (int)$user_id . " AND
          (EXISTS(SELECT * FROM `custom_reviews_tags` crt, `campaigns_tags` ct
                WHERE ct.`tags_id`=crt.`tags_id` and crt.`custom_reviews_id`=cr.`id` and ct.`campaigns_id`=" .
            (int)$campaing_id . ") or not exists (select * from campaigns_tags ct where ct.campaigns_id=" . (int)$campaing_id . "))";

        $return = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $return[] = $row;
            }
            $result->free();
        }

        return $return;
    }

    public function updateCustomReviewsTempTags($user_id)
    {
        $sql = "SELECT `id`,`temp_tags` FROM `custom_reviews` WHERE `user_id`=" . $user_id . " and `temp_tags`<>''";


        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $review_id = (int)$row['id'];

                $sql = "DELETE FROM `custom_reviews_tags` WHERE `custom_reviews_id`=" . $review_id;
                $this->connection->query($sql);

                if ($tags = explode(",", $row['temp_tags'])) {
                    foreach ($tags as $tag) {
                        $tag = trim($tag);

                        if ($tag != '') {
                            $sql = "INSERT INTO `tags`(`user_id`,`name`) VALUES( ?,? ) ON DUPLICATE KEY UPDATE `id`=LAST_INSERT_ID(`id`)";
                            $stmt2 = $this->connection->prepare($sql);
                            $stmt2->bind_param(
                                'is',
                                $user_id,
                                $tag
                            );
                            $stmt2->execute();
                            $tags_id = $this->connection->insert_id;


                            $sql = "INSERT INTO `custom_reviews_tags`(`tags_id`,`custom_reviews_id`) VALUES(?, ?)";
                            $stmt3 = $this->connection->prepare($sql);
                            $stmt3->bind_param(
                                'ii',
                                $tags_id,
                                $review_id
                            );
                            $stmt3->execute();
                        }
                    }
                }
                $sql = "UPDATE `custom_reviews` SET `temp_tags`='' WHERE `id`=" . $review_id;
                $this->connection->query($sql);
            }

            $result->free();
        }
    }

    public function updateCustomReviewsImages($user_id)
    {
        require_once(__DIR__ . '/tools.php');

        $sql = "SELECT `id`,`photo`,`icon` FROM `custom_reviews` WHERE `user_id`=" . $user_id . " and ((`icon` like 'http%') OR (`photo` like 'http%') )";
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                if (strpos($row['icon'], 'http') === 0) {
                    $rand = $user_id . '_' . time() . '_' . 'icon';
                    copy($row['icon'], sys_get_temp_dir() . '/' . $rand);
                    $target_file = $rand . '.png';

                    $res = Tools::uploadImage(sys_get_temp_dir() . '/' . $rand, __DIR__ . '/../uploads/custom_reviews/' . $target_file, 30, 30, 2 * 1024 * 1024);

                    if ($res['success'] == true) {
                        $sql = "UPDATE `custom_reviews` SET `icon`='" . $target_file . "' WHERE `id`=" . $row['id'];
                        $this->connection->query($sql);
                        $res[] = $sql;
                    }
                    @unlink(sys_get_temp_dir() . '/' . $rand);
                }

                if (strpos($row['photo'], 'http') === 0) {
                    $rand = $user_id . '_' . time() . '_' . 'photo';
                    copy($row['photo'], sys_get_temp_dir() . '/' . $rand);
                    $target_file = $rand . '.png';

                    $res = Tools::uploadImage(sys_get_temp_dir() . '/' . $rand, __DIR__ . '/../uploads/' . $target_file, 200, 150, 2 * 1024 * 1024);

                    if ($res['success'] == true) {
                        $sql = "UPDATE `custom_reviews` SET `photo`='" . $target_file . "' WHERE `id`=" . $row['id'];
                        $this->connection->query($sql);
                    }
                    @unlink(sys_get_temp_dir() . '/' . $rand);
                }
            }
        }
        return true;
    }

    public function getCaptureReviewsByUserId($id)
    {
        $sql = 'SELECT (select GROUP_CONCAT(t.`name`) from `tags` t, `capture_reviews_tags` ct
                        where ct.`capture_reviews_id`=cr.`id` and t.`id`=ct.`tags_id`) tags, `id`, `user_id`, `title`, `date_add`, `logo`, `name_of_business`, `page_title`, `description` from `capture_reviews` cr where cr.`user_id` =' . (int)$id;

        $capture_reviews = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $capture_reviews[$row['id']] = $row;
            }
            $result->free();
        }


        return $capture_reviews;
    }

    public function getCaptureReviewsById($id)
    {
        $sql = "SELECT
        (select GROUP_CONCAT(t.`name`) from `tags` t, `capture_reviews_tags` ct
                        where ct.`capture_reviews_id`=cr.`id` and t.`id`=ct.`tags_id`) tags,
                  cr.*  FROM `capture_reviews` cr where cr.`id` = " . (int)$id;
        $this->connection->query($sql);


        $return = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $return = $row;
            }
            $result->free();
        }


        return $return;
    }

      public function getCaptureReviewsByIdUserId($id, $user_id)
    {


        $sql = "SELECT
        (select GROUP_CONCAT(t.`name`) from `tags` t, `capture_reviews_tags` ct
                        where ct.`capture_reviews_id`=cr.`id` and t.`id`=ct.`tags_id`) tags,
                  cr.*  FROM `capture_reviews` cr where cr.`id` = " . (int)$id . " and cr.`user_id` = " . $user_id;


        $this->connection->query($sql);


        $return = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $return = $row;
            }
            $result->free();
        }


        return $return;
    }

    public function addCaptureReviews($data)
    {
        $current_date = gmdate('Y-m-d H:i:s');
        $sql = "INSERT INTO `capture_reviews`(`user_id`,  `date_add`) VALUES( ?, ?)";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            'is',
            $data['user_id'],
            $current_date
        );

        if ($stmt->execute()) {
            return $this->connection->insert_id;
        } else {
            return 0;
        }
    }

    public function updateCaptureReviews($data)
    {



        $sql = "UPDATE `capture_reviews` SET
          `share_linkedin`=?,
          `share_facebook`=?,
          `share_twitter`=?,
          `title`=?,
          `type` = ?,
          `logo`=?,
          `name_of_business`=?,
          `page_title`=?,
          `description`=?,
          `reward`=?,
          `reward_webhook`=?,
          `footer_text`=?,
          `min_rating`=?,
          `redirect_url`=?,

          `enable_review_directories_google`=?,
          `review_directories_google` =? ,
          `enable_review_directories_facebook` = ?,
          `review_directories_facebook` = ? ,
          `enable_review_directories_yelp` = ?,
          `review_directories_yelp` = ?,
          `enable_review_directories_custom` = ?,
          `review_directories_custom` = ? ,
          `custom_logo` = ?,
          `primary_font_family` = ?,
          `primary_font_color` = ?,
          `secondary_font_family` = ?,
          `secondary_font_color` = ?,
          `enable_email_for_receiving_negative_review` = ?,
          `email_for_receiving_negative_review` = ?,
          `enable_short_io` = ?,
          `short_io_api_key` = ?,
          `short_io_domain` = ?,
          `youtube` = ?,
          `enable_google_sheets` = ?,
          `review_template_id` = ?
          WHERE `id`=? and `user_id`=?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            'iiisssssssssdsisisisissssssisisssiiii',
            $data['share_linkedin'],
            $data['share_facebook'],
            $data['share_twitter'],
            $data['title'],
            $data['type'],
            $data['logo'],
            $data['name_of_business'],
            $data['page_title'],
            $data['description'],
            $data['reward'],
            $data['reward_webhook'],
            $data['footer_text'],
            $data['min_rating'],
            $data['redirect_url'],

            $data['enable_review_directories_google'],
            $data['review_directories_google'],

            $data['enable_review_directories_facebook'],
            $data['review_directories_facebook'],

            $data['enable_review_directories_yelp'],
            $data['review_directories_yelp'],


            $data['enable_review_directories_custom'],
            $data['review_directories_custom'],

            $data['custom_logo'],
            $data['primary_font_family'],
            $data['primary_font_color'],
            $data['secondary_font_family'],
            $data['secondary_font_color'],

            $data['enable_email_for_receiving_negative_review'],
            $data['email_for_receiving_negative_review'],
            $data['enable_short_io'],
            $data['short_io_api_key'],
            $data['short_io_domain'],

            $data['youtube'],
            $data['enable_google_sheets'],
            $data['review_template_id'],

            $data['id'],
            $data['user_id']
        );

        if ($stmt->execute()) {
            return true;
        } else {
            return $this->connection->error;
        }
    }


    public function cloneCaptureReviewCampaignById($id)
    {
        $sql = 'INSERT into capture_reviews (
        `user_id`, `title`, `date_add`, `logo`, `name_of_business`, `page_title`, `description`, `reward`, `reward_webhook`, `footer_text`, `min_rating`, `redirect_url`, `google_access_token`, `google_refresh_token`, `google_spread_sheet_id`, `google_sheet_id`, `enable_review_directories_google`, `review_directories_google`,
                        `enable_review_directories_yelp`, `review_directories_yelp`, `enable_review_directories_custom`, `review_directories_custom`,
                        `enable_review_directories_facebook`, `review_directories_facebook`, `custom_logo`, `enable_email_for_receiving_negative_review`,
                        `email_for_receiving_negative_review`, `primary_font_family`, `primary_font_color`, `secondary_font_family`, `secondary_font_color`, `enable_short_io`, `short_io_api_key`, `short_io_domain`

      )

      SELECT `user_id`, CONCAT(`title`," copy"), `date_add`, `logo`, `name_of_business`, `page_title`, `description`, `reward`, `reward_webhook`, `footer_text`, `min_rating`, `redirect_url`, `google_access_token`, `google_refresh_token`, `google_spread_sheet_id`, `google_sheet_id`, `enable_review_directories_google`, `review_directories_google`,
                      `enable_review_directories_yelp`, `review_directories_yelp`, `enable_review_directories_custom`, `review_directories_custom`,
                      `enable_review_directories_facebook`, `review_directories_facebook`, `custom_logo`, `enable_email_for_receiving_negative_review`,
                      `email_for_receiving_negative_review`, `primary_font_family`, `primary_font_color`, `secondary_font_family`, `secondary_font_color`, `enable_short_io`, `short_io_api_key`, `short_io_domain`
                       FROM `capture_reviews` WHERE `id`=?';

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            'i',
            $id
        );
        $stmt->execute();
        return true;
    }

    public function updateCaptureReviewsGoogleTokens($data)
    {

        $sql = "UPDATE `capture_reviews` SET
           `google_access_token`=?,
           `google_refresh_token`=?
            WHERE `id`=? and `user_id`=?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            'ssii',
            $data['google_access_token'],
            $data['google_refresh_token'],
            $data['id'],
            $data['user_id']
        );

        if ($stmt->execute()) {
            return true;
        } else {
            return $this->connection->error;
        }
    }


    public function updateCaptureReviewsGoogleSheetID($data)
    {
        $sql = "UPDATE `capture_reviews` SET
           `google_spread_sheet_id`=?,
           `google_sheet_id`=?
            WHERE `id`=? and `user_id`=?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            'ssii',
            $data['google_spread_sheet_id'],
            $data['google_sheet_id'],
            $data['id'],
            $data['user_id']
        );

        if ($stmt->execute()) {
            return true;
        } else {
            return $this->connection->error;
        }
    }

    public function insertReviewTemplate($name, $template, $preview_image, $islive, $forfreeuser)
    {
        $forfree = $forfreeuser ? 1 : 0;
        $isli = $islive ? 1 : 0;
        $sql = "INSERT INTO `review_templates`(`name`, `template`, `preview_image`,`islive`,`forfreeuser`) VALUES(?, ?, ?, ?, ?)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            'sssii',
            $name,
            $template,
            $preview_image,
            $isli,
            $forfree
        );

        if ($stmt->execute()) {
            return true;
        } else {
            return $this->connection->error;
        }
    }

    public function getReviewTemplates()
    {
        $sql = 'SELECT * FROM `review_templates`';

        $review_templates = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $review_templates[$row['id']] = $row;
            }
            $result->free();
        }


        return $review_templates;
    }
    public function getReviewTemplatesonlylive($userid)
    {
        $paln = $this->getCurrentUserPlan($userid);
       // $plan['plan_name'];
      /*  echo '<script type="text/JavaScript">
     alert('.$paln[0]['plan_name'].');
     </script>';
        die();*/

        $sql = '';

        if ($paln['plan_name'] == 'Free Plan' || $paln['plan_name'] == 'test plan !') {
            $sql = 'SELECT * FROM `review_templates` where `islive`=1 and `forfreeuser`=1 ';
        } else {
            $sql = 'SELECT * FROM `review_templates` where `islive`=1  ';
        }


        $review_templates = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $review_templates[$row['id']] = $row;
            }
            $result->free();
        }


        return $review_templates;
    }
    public function deleteReviewTemplate($templateId)
    {
        $sql = "DELETE FROM `review_templates` WHERE `id` = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('i', $templateId);

        if ($stmt->execute()) {
            return true;
        } else {
            return $this->connection->error;
        }
    }

    public function getReviewTemplate($templateId)
    {
        $sql = 'SELECT * FROM `review_templates` WHERE `id` = ' . $templateId . ' LIMIT 1';

        $review_templates = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $review_templates[$row['id']] = $row;
            }
            $result->free();
        }

        return $review_templates[$templateId];
    }

    /*
    public function updateReviewTemplate($name, $template, $preview_image, $id)
    {
        $sql = "UPDATE `review_templates` SET
        `name` = ?,
        `template` = ?,
        `preview_image` = ?
        WHERE `id` = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            'sssi',
            $name,
            $template,
            $preview_image,
            $id
        );

        if ($stmt->execute()) {
            return true;
        } else {
            return $this->connection->error;
        }
    }*/


    public function updateReviewTemplate($name, $template, $preview_image,  $islive, $forfreeuser, $id)
    {
        $forfree = $forfreeuser ? 1 : 0;
        $isli = $islive ? 1 : 0;
        $sql = "UPDATE `review_templates` SET
        `name` = ?,
        `template` = ?,
        `preview_image` = ?,
        `islive` = ?,
        `forfreeuser` = ?

        WHERE `id` = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            'sssiii',
            $name,
            $template,
            $preview_image,
            $isli,
            $forfree,
            $id
        );

        if ($stmt->execute()) {
            return true;
        } else {
            return $this->connection->error;
        }
    }


    public function updateUserType($userId, $type)
    {
        $sql = "UPDATE `user` SET
        `type` = ?
        WHERE `id` = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            'ii',
            $type,
            $userId
        );

        if ($stmt->execute()) {
            return true;
        } else {
            return $this->connection->error;
        }
    }

    /**
     * Function to load review data for embed video ladnding page
     * Used for sharing feature
     * @param $id integer Review Id
     */
    public function getCustomReviewByReviewId($id)
    {
        $sql = "SELECT cr.name, cr.rating, cr.review, cr.loom_url FROM `custom_reviews` AS cr WHERE cr.id = " . (int)$id;
        $this->connection->query($sql);


        $return = null;
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $return = $row;
            }
            $result->free();
        }


        return $return;
    }
}
