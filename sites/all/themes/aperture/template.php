<?php
/**
 * @file
 * Contains the theme's functions to manipulate Drupal's default markup.
 *
 * A QUICK OVERVIEW OF DRUPAL THEMING
 *
 *   The default HTML for all of Drupal's markup is specified by its modules.
 *   For example, the comment.module provides the default HTML markup and CSS
 *   styling that is wrapped around each comment. Fortunately, each piece of
 *   markup can optionally be overridden by the theme.
 *
 *   Drupal deals with each chunk of content using a "theme hook". The raw
 *   content is placed in PHP variables and passed through the theme hook, which
 *   can either be a template file (which you should already be familiary with)
 *   or a theme function. For example, the "comment" theme hook is implemented
 *   with a comment.tpl.php template file, but the "breadcrumb" theme hooks is
 *   implemented with a theme_breadcrumb() theme function. Regardless if the
 *   theme hook uses a template file or theme function, the template or function
 *   does the same kind of work; it takes the PHP variables passed to it and
 *   wraps the raw content with the desired HTML markup.
 *
 *   Most theme hooks are implemented with template files. Theme hooks that use
 *   theme functions do so for performance reasons - theme_field() is faster
 *   than a field.tpl.php - or for legacy reasons - theme_breadcrumb() has "been
 *   that way forever."
 *
 *   The variables used by theme functions or template files come from a handful
 *   of sources:
 *   - the contents of other theme hooks that have already been rendered into
 *     HTML. For example, the HTML from theme_breadcrumb() is put into the
 *     $breadcrumb variable of the page.tpl.php template file.
 *   - raw data provided directly by a module (often pulled from a database)
 *   - a "render element" provided directly by a module. A render element is a
 *     nested PHP array which contains both content and meta data with hints on
 *     how the content should be rendered. If a variable in a template file is a
 *     render element, it needs to be rendered with the render() function and
 *     then printed using:
 *       <?php print render($variable); ?>
 *
 * ABOUT THE TEMPLATE.PHP FILE
 *
 *   The template.php file is one of the most useful files when creating or
 *   modifying Drupal themes. With this file you can do three things:
 *   - Modify any theme hooks variables or add your own variables, using
 *     preprocess or process functions.
 *   - Override any theme function. That is, replace a module's default theme
 *     function with one you write.
 *   - Call hook_*_alter() functions which allow you to alter various parts of
 *     Drupal's internals, including the render elements in forms. The most
 *     useful of which include hook_form_alter(), hook_form_FORM_ID_alter(),
 *     and hook_page_alter(). See api.drupal.org for more information about
 *     _alter functions.
 *
 * OVERRIDING THEME FUNCTIONS
 *
 *   If a theme hook uses a theme function, Drupal will use the default theme
 *   function unless your theme overrides it. To override a theme function, you
 *   have to first find the theme function that generates the output. (The
 *   api.drupal.org website is a good place to find which file contains which
 *   function.) Then you can copy the original function in its entirety and
 *   paste it in this template.php file, changing the prefix from theme_ to
 *   aperture_. For example:
 *
 *     original, found in modules/field/field.module: theme_field()
 *     theme override, found in template.php: aperture_field()
 *
 *   where aperture is the name of your sub-theme. For example, the
 *   zen_classic theme would define a zen_classic_field() function.
 *
 *   Note that base themes can also override theme functions. And those
 *   overrides will be used by sub-themes unless the sub-theme chooses to
 *   override again.
 *
 *   Zen core only overrides one theme function. If you wish to override it, you
 *   should first look at how Zen core implements this function:
 *     theme_breadcrumbs()      in zen/template.php
 *
 *   For more information, please visit the Theme Developer's Guide on
 *   Drupal.org: http://drupal.org/node/173880
 *
 * CREATE OR MODIFY VARIABLES FOR YOUR THEME
 *
 *   Each tpl.php template file has several variables which hold various pieces
 *   of content. You can modify those variables (or add new ones) before they
 *   are used in the template files by using preprocess functions.
 *
 *   This makes THEME_preprocess_HOOK() functions the most powerful functions
 *   available to themers.
 *
 *   It works by having one preprocess function for each template file or its
 *   derivatives (called theme hook suggestions). For example:
 *     THEME_preprocess_page    alters the variables for page.tpl.php
 *     THEME_preprocess_node    alters the variables for node.tpl.php or
 *                              for node--forum.tpl.php
 *     THEME_preprocess_comment alters the variables for comment.tpl.php
 *     THEME_preprocess_block   alters the variables for block.tpl.php
 *
 *   For more information on preprocess functions and theme hook suggestions,
 *   please visit the Theme Developer's Guide on Drupal.org:
 *   http://drupal.org/node/223440 and http://drupal.org/node/1089656
 */

function getNotification() {
    global $user;

    $fname = db_query("SELECT n.field_first_name_value FROM {field_data_field_first_name} n WHERE n.entity_id = $user->uid")->fetchField();
    $lname = db_query("SELECT n.field_last_name_value FROM {field_data_field_last_name} n WHERE n.entity_id = $user->uid")->fetchField();
    $phone = db_query("SELECT n.field_phone_number_value FROM {field_data_field_phone_number} n WHERE n.entity_id = $user->uid")->fetchField();

    /* there can be more than one preference, duh */

    $pref[] =  db_query("SELECT n.field_athlete_group_preference_value FROM {field_data_field_athlete_group_preference} n WHERE n.entity_id = $user->uid")

    $image = db_query("SELECT n.uri FROM {file_managed} n WHERE n.fid = $user->picture")->fetchField();
    $imageURL = "http://codingroup.com/aperture/admin/images/no-image.png";
    $timeOffset = getTimeOffset();
    
    if (!$pref) {
        $pref[0] = "none";
    }
    if ($image) {
        $imageURL = file_create_url($image);
    }
    
    // Query for getting today's events
    $q1 =  "SELECT n.title,";
    $q1 .= "  LOWER(DATE_FORMAT(DATE_ADD(d.field_event_date_value, INTERVAL $timeOffset HOUR), '%l:%i%p')) as start,";
    $q1 .= "  LOWER(DATE_FORMAT(DATE_ADD(d.field_event_date_value2, INTERVAL $timeOffset HOUR), '%l:%i%p')) as end";
    $q1 .= " FROM node n";
    $q1 .= "  INNER JOIN field_data_field_event_date d";
    $q1 .= "  ON n.nid = d.entity_id";
    $q1 .= " WHERE n.uid = $user->uid";
    $q1 .= " AND d.field_event_date_value > NOW()";
    $q1 .= " AND d.field_event_date_value < DATE_ADD(NOW(), INTERVAL 1 DAY)";
    
    // Query for getting tomorrow's events
    $q2 =  "SELECT n.title,";
    $q2 .= "  DATE_FORMAT(DATE_ADD(d.field_event_date_value, INTERVAL $timeOffset HOUR), '%m/%d/%y') as date,";
    $q2 .= "  LOWER(DATE_FORMAT(DATE_ADD(d.field_event_date_value, INTERVAL $timeOffset HOUR), '%l:%i%p')) as start,";
    $q2 .= "  LOWER(DATE_FORMAT(DATE_ADD(d.field_event_date_value2, INTERVAL $timeOffset HOUR), '%l:%i%p')) as end"; 
    $q2 .= " FROM node n";
    $q2 .= "  INNER JOIN field_data_field_event_date d";
    $q2 .= "  ON n.nid = d.entity_id";
    $q2 .= " WHERE n.uid = $user->uid";
    $q2 .= "  AND d.field_event_date_value > DATE_ADD(NOW(), INTERVAL 1 DAY)";
    $q2 .= "  AND d.field_event_date_value < DATE_ADD(NOW(), INTERVAL 2 DAY)";
     
    // Query for getting the day after tomorrow's events
    $q3 =  "SELECT n.title,";
    $q3 .= "  DATE_FORMAT(DATE_ADD(d.field_event_date_value, INTERVAL $timeOffset HOUR), '%m/%d/%y') as date,";
    $q3 .= "  LOWER(DATE_FORMAT(DATE_ADD(d.field_event_date_value, INTERVAL $timeOffset HOUR), '%l:%i%p')) as start,";
    $q3 .= "  LOWER(DATE_FORMAT(DATE_ADD(d.field_event_date_value2, INTERVAL $timeOffset HOUR), '%l:%i%p')) as end";
    $q3 .= " FROM node n";
    $q3 .= "  INNER JOIN field_data_field_event_date d";
    $q3 .= "  ON n.nid = d.entity_id";
    $q3 .= " WHERE n.uid = $user->uid";
    $q3 .= "  AND d.field_event_date_value > DATE_ADD(NOW(), INTERVAL 2 DAY)";
    $q3 .= "  AND d.field_event_date_value < DATE_ADD(NOW(), INTERVAL 3 DAY)";
    ?>
    <div class="main-box">
        <h1>Notification Center</h1>
        <div class="sub-header">
            <!-- 1st BLOCK -->
            <h2>today</h2>
            <div class="content">
                <ul class="list-icon">
                <?php
                $result = db_query($q1);
                while ($record = $result->fetchAssoc()) { ?>
                    <li><?php echo $record["title"]; ?> Today <?php echo $record["start"]; ?> to <?php echo $record["end"]; ?> </li>
                <?php } ?>
                </ul>
            </div>
            <!-- END 1st BLOCK -->

            <!-- 2nd BLOCK -->
            <h2>tomorrow</h2>
            <div class="content">
                <ul class="list-icon">
                <?php
                $result = db_query($q2);
                while ($record = $result->fetchAssoc()) { ?>
                    <li><?php echo $record["title"]; ?> <?php echo $record["date"]; ?> <?php echo $record["start"]; ?> to <?php echo $record["end"]; ?> </li>
                <?php } ?>
                </ul>
            </div>
            <!-- END 2nd BLOCK -->

            <!-- 3nd BLOCK -->
            <h2>day after tomorrow</h2>
            <div class="content">
                <ul class="list-icon">
                <?php
                $result = db_query($q3);
                while ($record = $result->fetchAssoc()) { ?>
                    <li><?php echo $record["title"]; ?> <?php echo $record["date"]; ?> <?php echo $record["start"]; ?> to <?php echo $record["end"]; ?> </li>
                <?php } ?>
                </ul>
            </div>
            <!-- END 3nd BLOCK -->
        </div>
    </div>

    <div class="right-sidebar">
        <img src="<?php echo $imageURL; ?>" />
        <hr />
    <?php
    $numMessages = privatemsg_unread_count();
    if ($numMessages > 0) { ?>
        <h3>New Messages <span style="color:red; margin-left: 5.5em;"><a class="inboxCount" href="?q=messages"><?php echo $numMessages; ?></a></span></h3>
    <?php } else { ?>
        <h3>No New Messages</h3>
    <?php } ?>
        <hr />
        <h4>Personal Info <span style="color:#f1f1f1; margin-left: 5.0em;"><a href="?q=user/<?php global $user; echo $user->uid; ?>/edit">EDIT</a></span></h4>
        <div id="personal-info">
            <span style="color:#b1b1b1">Name:</span> <?php echo $fname; ?> <?php echo $lname; ?>
            <br />
            <span style="color:#b1b1b1">Email:</span> <?php echo $user->mail; ?>
            <br />
            <span style="color:#b1b1b1">Phone:</span> <?php echo $phone; ?>
            <br />
	    <span style="color:#b1b1b1">Tutoring Preferences:</span><br />
		<?php 
			$n = 0; 
			do {
				echo $pref[$n];
				?><br /><?php 
				$n++;
			} while ($n < count($pref));
		?>
            <br />
        </div>
    </div>
<?php }

function createHello() {?>
    <p>Hello there.</p>
<?php }

/**
 * PHP function to get the offset from GMT to America/Los_Angeles. This 
 * function will automatically determine whether it is -7 or -8 hours.
 * 
 * @return Returns the timezone offset as an int.
 */
function getTimeOffset() {
    $userTimezone = new DateTimeZone("America/Los_Angeles");
    $gmtTimezone = new DateTimeZone("GMT");
    $myDateTime = new DateTime("2009-03-21 13:14", $gmtTimezone);
    
    return ($userTimezone->getOffset($myDateTime) / 3600);
}

/**
 * Override or insert variables into the maintenance page template.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("maintenance_page" in this case.)
 */
/* -- Delete this line if you want to use this function
function aperture_preprocess_maintenance_page(&$variables, $hook) {
  // When a variable is manipulated or added in preprocess_html or
  // preprocess_page, that same work is probably needed for the maintenance page
  // as well, so we can just re-use those functions to do that work here.
  aperture_preprocess_html($variables, $hook);
  aperture_preprocess_page($variables, $hook);
}
// */

/**
 * Override or insert variables into the html templates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("html" in this case.)
 */
/* -- Delete this line if you want to use this function
function aperture_preprocess_html(&$variables, $hook) {
  $variables['sample_variable'] = t('Lorem ipsum.');

  // The body tag's classes are controlled by the $classes_array variable. To
  // remove a class from $classes_array, use array_diff().
  //$variables['classes_array'] = array_diff($variables['classes_array'], array('class-to-remove'));
}
// */

/**
 * Override or insert variables into the page templates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("page" in this case.)
 */
/* -- Delete this line if you want to use this function
function aperture_preprocess_page(&$variables, $hook) {
  $variables['sample_variable'] = t('Lorem ipsum.');
}
// */

/**
 * Override or insert variables into the node templates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("node" in this case.)
 */
/* -- Delete this line if you want to use this function
function aperture_preprocess_node(&$variables, $hook) {
  $variables['sample_variable'] = t('Lorem ipsum.');

  // Optionally, run node-type-specific preprocess functions, like
  // aperture_preprocess_node_page() or aperture_preprocess_node_story().
  $function = __FUNCTION__ . '_' . $variables['node']->type;
  if (function_exists($function)) {
    $function($variables, $hook);
  }
}
// */

/**
 * Override or insert variables into the comment templates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("comment" in this case.)
 */
/* -- Delete this line if you want to use this function
function aperture_preprocess_comment(&$variables, $hook) {
  $variables['sample_variable'] = t('Lorem ipsum.');
}
// */

/**
 * Override or insert variables into the region templates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("region" in this case.)
 */
/* -- Delete this line if you want to use this function
function aperture_preprocess_region(&$variables, $hook) {
  // Don't use Zen's region--sidebar.tpl.php template for sidebars.
  //if (strpos($variables['region'], 'sidebar_') === 0) {
  //  $variables['theme_hook_suggestions'] = array_diff($variables['theme_hook_suggestions'], array('region__sidebar'));
  //}
}
// */

/**
 * Override or insert variables into the block templates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("block" in this case.)
 */
/* -- Delete this line if you want to use this function
function aperture_preprocess_block(&$variables, $hook) {
  // Add a count to all the blocks in the region.
  // $variables['classes_array'][] = 'count-' . $variables['block_id'];

  // By default, Zen will use the block--no-wrapper.tpl.php for the main
  // content. This optional bit of code undoes that:
  //if ($variables['block_html_id'] == 'block-system-main') {
  //  $variables['theme_hook_suggestions'] = array_diff($variables['theme_hook_suggestions'], array('block__no_wrapper'));
  //}
}
// */
