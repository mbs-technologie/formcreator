<?php
class PluginFormcreatorWizard {

   const MENU_CATALOG      = 1;
   const MENU_LAST_FORMS   = 2;
   const MENU_RESERVATIONS = 3;
   const MENU_FEEDS        = 4;
   const MENU_BOOKMARKS    = 5;

   public static function header($title) {
      global $CFG_GLPI, $HEADER_LOADED, $PLUGIN_HOOKS, $DB;

      // Print a nice HTML-head for help page
      if ($HEADER_LOADED) {
         return;
      }
      $HEADER_LOADED = true;

      // force layout of glpi
      $_SESSION['glpilayout'] = "lefttab";

      Html::includeHeader($title);

      $body_class = "layout_".$_SESSION['glpilayout'];
      if ((strpos($_SERVER['REQUEST_URI'], "form.php") !== false)
            && isset($_GET['id']) && ($_GET['id'] > 0)) {
         if (!CommonGLPI::isLayoutExcludedPage()) {
            $body_class.= " form";
         } else {
            $body_class = "";
         }
      }
      echo "<body class='$body_class' id='plugin_formcreator_serviceCatalog'>";

      echo '<div class="plugin_formcreator_container">';
      echo '<div id="header" class ="plugin_formcreator_leftHeader">';
      echo '<div id="header_top">';

      echo '<div class="plugin_formcreator_userMenuCell">';

      // avatar
      $user = new User;
      $user->getFromDB($_SESSION['glpiID']);
      echo '<a href="'.$CFG_GLPI["root_doc"].'/front/preference.php"
               title="'.formatUserName (0, $_SESSION["glpiname"],
                                           $_SESSION["glpirealname"],
                                           $_SESSION["glpifirstname"], 0, 20).'">';
      echo '<span id="plugin_formcreator_avatar">
            <img src="'.User::getThumbnailURLForPicture($user->fields['picture']).'"/>
            </span>';
      echo '</a>';

      // icons
      echo '<ul class="plugin_formcreator_userMenu_icons">';
      echo '<li id="plugin_formcreator_preferences_icon">';
      echo '<a href="'.$CFG_GLPI["root_doc"].'/front/preference.php" title="'.
            __s('My settings').'"><span id="preferences_icon" title="'.__s('My settings').'" alt="'.__s('My settings').'" class="button-icon"></span>';
      echo '</a></li>';

      // Logout
      echo '<li id="plugin_formcreator_logoutIcon" ><a href="'.$CFG_GLPI["root_doc"].'/front/logout.php';      /// logout witout noAuto login for extauth
      if (isset($_SESSION['glpiextauth']) && $_SESSION['glpiextauth']) {
         echo '?noAUTO=1';
      }
      echo '" title="'.__s('Logout').'">';
      echo '<span id="logout_icon" title="'.__s('Logout').'" alt="'.__s('Logout').'" class="button-icon"></span></a>';
      echo '</li>';

      echo '</ul>';

      echo '</div>';
      echo '<div id="c_logo"></div>';
      echo '</div>';


      // menu toggle (responsive mode)
      echo "<input type='checkbox' id='formcreator-toggle-nav'>";
      echo "<label for='formcreator-toggle-nav' class='formcreator-nav-button'></label>";

      echo '<div id="c_menu" class="plugin_formcreator_leftMenu">';

      // Left vertical menu
      $activeMenuItem = self::findActiveMenuItem();
      echo '<ul class="plugin_formcreator_services">';
      echo '<li class="' . ($activeMenuItem == self::MENU_CATALOG ? 'plugin_formcreator_selectedMenuItem' : '') . ' plugin_formcreator_serviceCatalogIcon">';
      echo '<span class="fc_list_icon"></span>';
      echo '<a href="' . $CFG_GLPI["root_doc"].'/plugins/formcreator/front/wizard.php' . '">' . __('Seek assistance', 'formcreator') . '</a></li>';

      echo '<li class="' . ($activeMenuItem == self::MENU_LAST_FORMS ? 'plugin_formcreator_selectedMenuItem' : '')  . ' plugin_formcreator_myRequestsIcon">';
      echo '<span class="fc_list_icon"></span>';
      echo '<a href="' . $CFG_GLPI["root_doc"].'/plugins/formcreator/front/issue.php' . '">' . __('My requests for assistance', 'formcreator') . '</a></li>';

      // show ticket summary
      $options = array('criteria' => array(array('field'      => 4,
                                                 'searchtype' => 'equals',
                                                 'value'      => 'process',
                                                 'link'       => 'AND',
                                                 'value'      => 'notold')),
                       'reset'    => 'reset');
      echo "<li id='formcreator_servicecatalogue_ticket_summary'>";
      $status_count = self::getTicketSummary(false);

      if (count($status_count[Ticket::INCOMING])) {
      echo "<span class='status status_incoming'>
            <a href='".FORMCREATOR_ROOTDOC."/front/issue.php?".
                    Toolbox::append_params($options,'&amp;')."'>
            <span class='status_number'>".
            $status_count[Ticket::INCOMING]."
            </span>
            <div class='status_label'>".__('Processing')."</div>
            </a>
            </span>";
      }

      if (count($status_count[Ticket::WAITING])) {
         $options['criteria'][0]['value'] = Ticket::WAITING;
         echo "<span class='status status_waiting'>
               <a href='".FORMCREATOR_ROOTDOC."/front/issue.php?".
                       Toolbox::append_params($options,'&amp;')."'>
               <span class='status_number'>".
               $status_count[Ticket::WAITING]."
               </span>
               <div class='status_label'>".__('Pending')."</div>
               </a>
               </span>";
      }

      if (count($status_count[Ticket::WAITING])) {
         $options['criteria'][0]['value'] = Ticket::WAITING;
         echo "<span class='status status_waiting'>
               <a href='".FORMCREATOR_ROOTDOC."/front/issue.php?".
                       Toolbox::append_params($options,'&amp;')."'>
               <span class='status_number'>".
               $status_count[Ticket::WAITING]."
               </span>
               <div class='status_label'>".__('To validate', 'formcreator')."</div>
               </a>
               </span>";
      }

      if (count($status_count[Ticket::SOLVED])) {
         $options['criteria'][0]['value'] = 'old';
         echo "<span class='status status_solved'>
               <a href='".FORMCREATOR_ROOTDOC."/front/issue.php?".
                       Toolbox::append_params($options,'&amp;')."'>
               <span class='status_number'>".
               $status_count[Ticket::SOLVED]."
               </span>
               <div class='status_label'>"._x('status', 'Solved')."</div>
               </a>
               </span>";
      }

      echo '</li>'; #formcreator_servicecatalogue_ticket_summary


      if (Session::haveRight("reservation", ReservationItem::RESERVEANITEM)) {
         $reservation_item = new reservationitem;
         $entity_filter = getEntitiesRestrictRequest("", 'glpi_reservationitems', 'entities_id',
                                                     $_SESSION['glpiactiveentities']);
         $found_available_res = $reservation_item->find($entity_filter);
         if (count($found_available_res)) {
            echo '<li class="' . ($activeMenuItem == self::MENU_RESERVATIONS ? 'plugin_formcreator_selectedMenuItem' : '')  . ' plugin_formcreator_reservationsIcon">';
            echo '<span class="fc_list_icon"></span>';
            echo '<a href="' . $CFG_GLPI["root_doc"].'/plugins/formcreator/front/reservationitem.php' . '">' . __('Book an asset', 'formcreator') . '</a></li>';
         }
      }

      if (RSSFeed::canView()) {
         echo '<li class="' . ($activeMenuItem == self::MENU_FEEDS ? 'plugin_formcreator_selectedMenuItem' : '')  . ' plugin_formcreator_feedsIcon">';
         echo '<span class="fc_list_icon"></span>';
         echo '<a href="' . $CFG_GLPI["root_doc"].'/plugins/formcreator/front/wizardfeeds.php' . '">' . __('Consult feeds', 'formcreator') . '</a></li>';
      }


      $query = "SELECT `glpi_bookmarks`.*,
                       `glpi_bookmarks_users`.`id` AS IS_DEFAULT
                FROM `glpi_bookmarks`
                LEFT JOIN `glpi_bookmarks_users`
                  ON (`glpi_bookmarks`.`itemtype` = `glpi_bookmarks_users`.`itemtype`
                      AND `glpi_bookmarks`.`id` = `glpi_bookmarks_users`.`bookmarks_id`
                      AND `glpi_bookmarks_users`.`users_id` = '".Session::getLoginUserID()."')
                WHERE `glpi_bookmarks`.`is_private`='1'
                  AND `glpi_bookmarks`.`users_id`='".Session::getLoginUserID()."'
                  OR `glpi_bookmarks`.`is_private`='0' ".
                     getEntitiesRestrictRequest("AND", "glpi_bookmarks", "", "", true);

      if ($result = $DB->query($query)) {
         if($numrows = $DB->numrows($result)) {
            echo '<li class="' . ($activeMenuItem == self::MENU_BOOKMARKS ? 'plugin_formcreator_selectedMenuItem' : '') . 'plugin_formcreator_bookmarksIcon">';
            Ajax::createIframeModalWindow('loadbookmark',
                  $CFG_GLPI["root_doc"]."/front/bookmark.php?action=load",
                  array('title'         => __('Load a bookmark'),
                        'reloadonclose' => true));
            echo '<span class="fc_list_icon"></span>';
            echo '<a href="#" onclick="$(\'#loadbookmark\').dialog(\'open\');">';
            echo __('Load a bookmark');
            echo '</a>';
            echo '</li>';
         }
      }

      echo '</ul>';

      // Profile and entity selection
      // check user id : header used for display messages when session logout
      echo '<ul class="plugin_formcreator_entityProfile">';
      if (Session::getLoginUserID()) {
         self::showProfileSelecter($CFG_GLPI["root_doc"]."/front/helpdesk.public.php");
      }
      echo '</ul>';

      echo '</div>';
      echo '</div>';

      echo '<div id="page" class="plugin_formcreator_page">';

      // call static function callcron() every 5min
      CronTask::callCron();

   }

   static function getTicketSummary($full = true) {
      global $DB;

      $can_group = Session::haveRight(Ticket::$rightname, Ticket::READGROUP)
                     && isset($_SESSION["glpigroups"])
                     && count($_SESSION["glpigroups"]);

      // construct query
      $query = "SELECT glpi_tickets.status,
                       COUNT(DISTINCT glpi_tickets.id) AS COUNT
                FROM glpi_tickets
                LEFT JOIN glpi_tickets_users
                  ON glpi_tickets.id = glpi_tickets_users.tickets_id
                  AND glpi_tickets_users.type = '".CommonITILActor::REQUESTER."'
                LEFT JOIN glpi_ticketvalidations
                  ON glpi_tickets.id = glpi_ticketvalidations.tickets_id";
      if ($can_group) {
         $query .= "
                LEFT JOIN glpi_groups_tickets
                  ON glpi_tickets.id = glpi_groups_tickets.tickets_id
                  AND glpi_groups_tickets.type = '".CommonITILActor::REQUESTER."'
               ";
      }
      $query .= getEntitiesRestrictRequest(" WHERE", "glpi_tickets");
      $query .= "
                  AND (
                     glpi_tickets_users.users_id = '".Session::getLoginUserID()."'
                     OR glpi_tickets.users_id_recipient = '".Session::getLoginUserID()."'
                     OR glpi_ticketvalidations.users_id_validate = '".Session::getLoginUserID()."'";

      if ($can_group) {
         $groups = implode(",",$_SESSION['glpigroups']);
         $query .= " OR glpi_groups_tickets.groups_id IN (".$groups.") ";
      }
      $query.= ")
            AND NOT glpi_tickets.is_deleted
         GROUP BY status";


      $status = array();
      $status_labels = Ticket::getAllStatusArray();
      foreach ($status_labels as $key => $label) {
         $status[$key] = 0;
      }

      $result = $DB->query($query);
      if ($DB->numrows($result) > 0) {
         while ($data = $DB->fetch_assoc($result)) {
            $status[$data["status"]] = $data["COUNT"];
         }
      }

      if (!$full) {
         $status[Ticket::INCOMING]+= $status[Ticket::ASSIGNED]
                                   + $status[Ticket::WAITING]
                                   + $status[Ticket::PLANNED];
         $status[Ticket::SOLVED]  += $status[Ticket::CLOSED];

         unset($status[Ticket::CLOSED],
               $status[Ticket::PLANNED],
               $status[Ticket::ASSIGNED]);
      }


      return $status;
   }

   public static function footer() {
      return Html::helpFooter();
   }

   protected static function findActiveMenuItem() {
      if (strpos($_SERVER['REQUEST_URI'], "formcreator/front/wizard.php") !== false
          || strpos($_SERVER['REQUEST_URI'], "formcreator/front/formdisplay.php") !== false
          || strpos($_SERVER['REQUEST_URI'], "formcreator/front/knowbaseitem.form.php") !== false) {
         return self::MENU_CATALOG;
      }
      if (strpos($_SERVER['REQUEST_URI'], "formcreator/front/issue.php") !== false
          || strpos($_SERVER['REQUEST_URI'], "formcreator/front/issue.form.php") !== false) {
         return self::MENU_LAST_FORMS;
      }
      if (strpos($_SERVER['REQUEST_URI'], "formcreator/front/reservationitem.php") !== false) {
         return self::MENU_RESERVATIONS;
      }
      if (strpos($_SERVER['REQUEST_URI'], "formcreator/front/wizardfeeds.php") !== false) {
         return self::MENU_FEEDS;
      }
      return false;
   }

   protected static function showProfileSelecter($target) {
      global $CFG_GLPI;

      if (count($_SESSION["glpiprofiles"]) > 1) {
         echo '<li><form name="form" method="post" action="'.$target.'">';
         $values = array();
         foreach ($_SESSION["glpiprofiles"] as $key => $val) {
            $values[$key] = $val['name'];
         }

         Dropdown::showFromArray('newprofile',$values,
               array('value'     => $_SESSION["glpiactiveprofile"]["id"],
                     'width'     => '150px',
                     'on_change' => 'submit()'));
         Html::closeForm();
         echo '</li>';
      }

      if (Session::isMultiEntitiesMode()) {
         echo '<li>';
         Ajax::createModalWindow('entity_window', $CFG_GLPI['root_doc']."/ajax/entitytree.php",
               array('title'       => __('Select the desired entity'),
                     'extraparams' => array('target' => $target)));
         echo "<a onclick='entity_window.dialog(\"open\");' href='#modal_entity_content' title=\"".
               addslashes($_SESSION["glpiactive_entity_name"]).
               "\" class='entity_select' id='global_entity_select'>".
               $_SESSION["glpiactive_entity_shortname"]."</a>";

         echo "</li>";
      }
   }

}
