<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * admin/tool/createusers.php
 *
 * @package    tool
 * @subpackage createusers
 * @copyright  2014 Gordon Bateson {@link http://quizport.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** Prevent direct access to this script */
defined('MOODLE_INTERNAL') || die;

/** Include required files */
require_once("$CFG->libdir/formslib.php");

/**
 * tool_createusers_form
 *
 * @package    tool
 * @subpackage createusers
 * @copyright  2014 Gordon Bateson (gordon.bateson@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 2.0
 */
class tool_createusers_form extends moodleform {

    var $numeric     = null;
    var $lowercase   = null;
    var $uppercase   = null;

    const TYPE_FIXED    = 1;
    const TYPE_RANDOM   = 2;
    const TYPE_SEQUENCE = 3;
    const TYPE_USERID   = 4;
    const TYPE_USERNAME = 5;

    const SIZE_INT      = 5;
    const SIZE_TEXT     = 10;
    const SIZE_LONGTEXT = 20;

    /**
     * constructor
     */
    public function tool_createusers_form($action=null, $customdata=null, $method='post', $target='', $attributes=null, $editable=true) {
        $this->numeric   = array_flip(str_split('23456789', 1));
        $this->lowercase = array_flip(str_split('abdeghjmnpqrstuvyz', 1));
        $this->uppercase = array_flip(str_split('ABDEGHJLMNPQRSTUVWXYZ', 1));
        parent::moodleform($action, $customdata, $method, $target, $attributes, $editable);
    }

    /**
     * definition
     */
    public function definition() {
        global $CFG, $DB, $USER;

        $mform = $this->_form;
        $tool = 'tool_createusers';
        $dot = get_string('stringseparator', $tool);

        // ==================================
        // usernames
        // ==================================
        //
        $this->add_heading($mform, 'usernames', $tool, true);

        // number of users
        $name = 'countusers';
        $label = get_string($name, $tool);
        $mform->addElement('text', $name, $label, array('size' => self::SIZE_INT));
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 20);

        // username prefix
        $name = 'usernameprefix';
        $label = get_string('prefix', $tool);
        $mform->addElement('text', $name, $label, array('size' => self::SIZE_TEXT));
        $mform->setType($name, PARAM_TEXT);
        $mform->setDefault($name, get_string('default'.$name, $tool).$dot);

        // username numeric type
        $name = 'usernametype';
        $label = get_string($name, $tool);
        $types = array(self::TYPE_USERID   => get_string('typeuserid', $tool),
                       self::TYPE_SEQUENCE => get_string('typesequence', $tool));
        $mform->addElement('select', $name, $label, $types);
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, self::TYPE_SEQUENCE);

        // username numeric width
        $name = 'usernamewidth';
        $label = get_string($name, $tool);
        $width = array_combine(range(1, 8), range(1, 8));
        $mform->addElement('select', $name, $label, $width);
        $mform->setType($name, PARAM_TEXT);
        $mform->setDefault($name, 2);

        // start users
        $name = 'startusers';
        $label = get_string($name, $tool);
        $mform->addElement('text', $name, $label, array('size' => self::SIZE_INT));
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 1);

        // increment users
        $name = 'incrementusers';
        $label = get_string($name, $tool);
        $mform->addElement('text', $name, $label, array('size' => self::SIZE_INT));
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 1);

        // username suffix
        $name = 'usernamesuffix';
        $label = get_string('suffix', $tool);
        $mform->addElement('text', $name, $label, array('size' => self::SIZE_TEXT));
        $mform->setType($name, PARAM_TEXT);
        $mform->setDefault($name, '');

        // ==================================
        // passwords
        // ==================================
        //
        $this->add_heading($mform, 'passwords', $tool, true);

        // password type
        $name = 'passwordtype';
        $label = get_string($name, $tool);
        $types = array(self::TYPE_FIXED    => get_string('typefixed',    $tool),
                       self::TYPE_USERNAME => get_string('typeusername', $tool),
                       self::TYPE_RANDOM   => get_string('typerandom',   $tool));
        $mform->addElement('select', $name, $label, $types);
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, self::TYPE_RANDOM);

        // password prefix
        $name = 'passwordprefix';
        $label = get_string('prefix', $tool);
        $mform->addElement('text', $name, $label, array('size' => self::SIZE_TEXT));
        $mform->setType($name, PARAM_TEXT);
        $mform->setDefault($name, array_rand($this->lowercase).$dot);

        // num of lowercase
        $name = 'countlowercase';
        $label = get_string($name, $tool);
        $mform->addElement('select', $name, $label, range(0,8));
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 0);

        // num of uppercase
        $name = 'countuppercase';
        $label = get_string($name, $tool);
        $mform->addElement('select', $name, $label, range(0,8));
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 0);

        // num of numeric
        $name = 'countnumeric';
        $label = get_string($name, $tool);
        $mform->addElement('select', $name, $label, range(0,8));
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 4);

        // shuffle random chars
        $name = 'shufflerandom';
        $label = get_string($name, $tool);
        $mform->addElement('selectyesno', $name, $label);
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 1);

        // password suffix
        $name = 'passwordsuffix';
        $label = get_string('suffix', $tool);
        $mform->addElement('text', $name, $label, array('size' => self::SIZE_TEXT));
        $mform->setType($name, PARAM_TEXT);
        $mform->setDefault($name, '');

        // ==================================
        // names
        // ==================================
        //
        $this->add_heading($mform, 'names', $tool, false);

        $types = array(self::TYPE_USERNAME => get_string('typeusername', $tool),
                       self::TYPE_FIXED    => get_string('typefixed',    $tool),
                       self::TYPE_SEQUENCE => get_string('typesequence', $tool),
                       self::TYPE_RANDOM   => get_string('typerandom',   $tool));

        $names = array('firstname', 'lastname', 'alternatename', 'screenname');
        foreach ($names as $name) {

            if (! property_exists($USER, $name)) {
                continue;
            }

            // type
            $type = $name.'type';
            $label = get_string($type, $tool);
            $mform->addElement('select', $type, $label, $types);
            $mform->setType($type, PARAM_INT);
            $mform->setDefault($type, self::TYPE_SEQUENCE);

            // prefix
            $prefix = $name.'prefix';
            $label = get_string('prefix', $tool);
            $mform->addElement('text', $prefix, $label, array('size' => self::SIZE_TEXT));
            $mform->setType($prefix, PARAM_TEXT);
            $mform->setDefault($prefix, get_string('default'.$name, $tool).$dot);

            // suffix
            $suffix = $name.'suffix';
            $label = get_string('suffix', $tool);
            $mform->addElement('text', $suffix, $label, array('size' => self::SIZE_TEXT));
            $mform->setType($suffix, PARAM_TEXT);
            $mform->setDefault($suffix, '');
        }

        // ==================================
        // student enrolments
        // ==================================
        //
        $this->add_heading($mform, 'studentenrolments', $tool, true);

        // reset grades
        $name = 'resetgrades';
        $label = get_string($name, $tool);
        $mform->addElement('selectyesno', $name, $label);
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 1);

        // cancel role assignments
        $name = 'cancelroles';
        $label = get_string($name, $tool);
        $mform->addElement('selectyesno', $name, $label);
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 1);

        // cancel current enrolments
        $name = 'cancelenrolments';
        $label = get_string($name, $tool);
        $mform->addElement('selectyesno', $name, $label);
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 1);

        // enrol as student in the following courses
        $name = 'enrolcourses';
        $label = get_string($name, $tool);
        $select = 'id <> ?';
        $params = array(SITEID);
        $courses = $DB->get_records_select_menu('course', $select, $params, 'shortname', 'id,shortname');
        $count = count($courses);
        if ($count <= 1) {
            $courses = array(0 => '') + $courses;
            $params = array();
        } else {
            $params = array('multiple' => 'multiple', 'size' => min($count, 5));
        }
        $mform->addElement('select', $name, $label, $courses, $params);
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 0);

        // enrol in the following groups
        $name = 'enrolgroups';
        $label = get_string($name, $tool);
        $mform->addElement('text', $name, $label, array('size' => self::SIZE_LONGTEXT));
        $mform->setType($name, PARAM_TEXT);
        $mform->setDefault($name, '');

        // ==================================
        // teacher enrolments
        // ==================================
        //
        $this->add_heading($mform, 'teacherenrolments', $tool, true);

        // teacher courses will be added to the following course category
        $name = 'enrolcategory';
        $label = get_string($name, $tool);
        $options = $DB->get_records_select_menu('course_categories', null, null, 'sortorder', 'id,name');
        $options = array(0 => '') + $options;
        $elements = array(
            $mform->createElement('select', $name, '', $options),
            $mform->createElement('text', $name.'name', '', array('size' => self::SIZE_LONGTEXT))
        );
        $mform->addGroup($elements, $name.'elements', $label, ' ', false);
        $mform->addHelpButton($name.'elements', $name, $tool);
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 0);
        $mform->setType($name.'name', PARAM_TEXT);
        $mform->setDefault($name.'name', '');

        // path to "filesystem" repository folder
        $name = 'folderpath';
        $label = get_string($name, $tool);
        $options = $this->get_moodledata_folders('repository');
        $mform->addElement('select', $name, $label, array(0 => '') + $options);
        $mform->setType($name, PARAM_PATH);
        $mform->setDefault($name, '');
        $mform->addHelpButton($name, $name, $tool);

        // use double-byte names
        $name = 'doublebyte';
        $label = get_string($name, $tool);
        $mform->addElement('selectyesno', $name, $label);
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 0);
        $mform->addHelpButton($name, $name, $tool);

        // reset courses i.e. remove course modules
        $name = 'resetcourses';
        $label = get_string($name, $tool);
        $mform->addElement('selectyesno', $name, $label);
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 1);
        $mform->addHelpButton($name, $name, $tool);

        // enrol the following students in each teacher's course
        $name = 'enrolstudents';
        $label = get_string($name, $tool);
        $select = $DB->sql_like('username', '?').' && deleted = ?';
        $params = array('%guest%', 0);
        if ($users = $DB->get_records_select('user', $select, $params, 'id', $this->get_userfields())) {
            foreach ($users as $userid => $user) {
                $users[$userid] = fullname($user);
            }
            $count = count($users);
        } else {
            $users = array();
            $count = 0;
        }
        if ($count <= 1) {
            $users = array(0 => '') + $users;
            $params = array();
        } else {
            $params = array('multiple' => 'multiple', 'size' => min($count, 5));
        }
        $mform->addElement('select', $name, $label, $users, $params);
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 0);
        $mform->addHelpButton($name, $name, $tool);

        // ==================================
        // defaults
        // (see user/editlib.php)
        // ==================================
        //
        $this->add_heading($mform, 'defaults', $tool, false);

        // timezone
        $name = 'timezone';
        $label = get_string($name);
        $default = '99';
        $zones = get_list_of_timezones();
        $zones[$default] = get_string('serverlocaltime');
        if (empty($CFG->forcetimezone) || $CFG->forcetimezone==$default) {
            $mform->addElement('select', $name, $label, $zones);
            $mform->setDefault($name, $default);
        } else {
            $zone = $zones[$CFG->forcetimezone];
            $mform->addElement('static', 'forcedtimezone', $label, $zone);
        }

        // lang
        $name = 'lang';
        $mform->addElement('select', $name, get_string('preferredlanguage'), get_string_manager()->get_list_of_translations());
        $mform->setType($name, PARAM_ALPHANUM);
        $mform->setDefault($name, $CFG->lang);

        // calendar
        if (file_exists($CFG->dirroot.'/calendar/classes/type_factory.php')) {
            // Moodle >= 2.6
            $types = \core_calendar\type_factory::get_list_of_calendar_types();
        } else {
            // Moodle <= 2.5
            $types = array();
        }
        $name = 'calendar';
        if (count($types) > 1) {
            $label = get_string('preferredcalendar', 'calendar');
            $mform->addElement('select', $name, $label, $types);
            $mform->setType($name, PARAM_ALPHA);
            $mform->setDefault($name, $CFG->calendartype);
        } else {
            $value = (empty($CFG->calendartype) ? '' : $CFG->calendartype);
            $mform->addElement('hidden', $name, $label);
            $mform->setType($name, PARAM_ALPHA);
        }

        // description
        $name = 'description';
        $mform->addElement('editor', $name, get_string('userdescription'));
        $mform->addHelpButton($name, 'userdescription');
        $mform->setType($name, PARAM_CLEANHTML);

        // set default description
        $element = $mform->getElement($name);
        $value = $element->getValue();
        if (is_array($value) && empty($value['text'])) {
            $value['text'] = get_string('defaultdescription', $tool);
            $element->setValue($value);
        }

        // ==================================
        // display
        // ==================================
        //
        $this->add_heading($mform, 'display', 'form', false);

        // show newuser
        $name = 'shownewuser';
        $label = get_string($name, $tool);
        $mform->addElement('selectyesno', $name, $label);
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 0);

        // show userid
        $name = 'showuserid';
        $label = get_string($name, $tool);
        $mform->addElement('selectyesno', $name, $label);
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 0);

        // show alternate name
        $name = 'showalternatename';
        if (property_exists($USER, 'alternatename')) {
            $label = get_string($name, $tool);
            $mform->addElement('selectyesno', $name, $label);
            $mform->setType($name, PARAM_INT);
            $mform->setDefault($name, 0);
        } else {
            $mform->addElement('hidden', $name, 0);
            $mform->setType($name, PARAM_INT);
        }

        // ==================================
        // action buttons
        // ==================================
        //
        $this->add_action_buttons(true, get_string('go'));

        // ==================================
        // javascript (if required)
        // ==================================
        //
        if (! method_exists($mform, 'setExpanded')) {
            // hide sections: names, defaults, display
            // include an external javascript file
            // to add show/hide buttons where needed
            $src = new moodle_url('/admin/tool/createusers/classes/form.js');
            $js = '<script type="text/javascript" src="'.$src.'"></script>';
            $mform->addElement('static', 'form_js', '', $js);
        }
    }

    /**
     * validation
     *
     * @param array $data
     * @param array $files
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $name = 'countusers';
        if (empty($data[$name])) {
            $errors[$name] = get_string('required');
        }

        $name = 'usernametype';
        if ($data[$name]==self::TYPE_USERID && empty($data['usernameprefix']) && empty($data['usernamesuffix'])) {
            $errors[$name] = 'USERID requires prefix or suffix';
        }

        return $errors;
    }

    /**
     * add_heading
     *
     * @param object $mform
     * @param string $name
     * @param string $plugin
     * @param boolean $expanded
     */
    public function add_heading($mform, $name, $plugin, $expanded) {
        $label = get_string($name, $plugin);
        $mform->addElement('header', $name, $label);
        if (method_exists($mform, 'setExpanded')) {
            $mform->setExpanded($name, $expanded);
        }
    }

    /**
     * create_users
     */
    public function create_users() {
        global $DB, $USER;

        // get form data
        $data = $this->get_data();
        $time = time();

        $OLD = '';
        $NEW = get_string('new');

        $columns = array();

        if (! empty($data->shownewuser)) {
            array_unshift($columns, 'newuser');
        }

        if (! empty($data->showuserid)) {
            array_unshift($columns, 'id');
        }

        // always show these columns
        array_push($columns, 'username', 'rawpassword', 'firstname', 'lastname');

        if (! empty($data->showalternatename)) {
            $columns[] = 'alternatename';
        }

        if (! empty($data->enrolcourses)) {
            $columns[] = 'courses';
            if (! empty($data->enrolgroups)) {
                $columns[] = 'groups';
            }
        }

        if (! empty($data->enrolcategoryname)) {
            $data->enrolcategory = $this->get_course_categoryid($data->enrolcategoryname, $data->enrolcategory);
        }

        if (! empty($data->enrolcategory)) {
            $columns[] = 'category';
        }

        $count = max($data->countusers, 0);
        $start = max($data->startusers, 0);
        $step  = max($data->incrementusers, 1);

        if ($data->usernametype==self::TYPE_USERID) {

            // get currently used ids
            $select = $DB->sql_like('username', '?');
            $params = array($data->usernameprefix.'%'.$data->usernamesuffix);
            if ($nums = $DB->get_records_select('user', $select, $params, null, 'id,username', 0, $count)) {
                $nums = array_keys($nums);
            } else {
                $nums = array();
            }

            // pad with unused ids
            if (count($nums) < $count) {
                $max = $DB->get_field('user', 'MAX(id)', array());
                for ($i=count($nums); $i<$count; $i++) {
                    $nums[$i] = ++$max;
                }
            }
        } else {
            $end  = $start + ($count * $step);
            $nums = range($start, $end, $step);
        }

        // create users
        $table = '';
        for ($i=0; $i<$data->countusers; $i++) {

            // create user
            $num = str_pad($nums[$i], $data->usernamewidth, '0', STR_PAD_LEFT);
            $user = $this->create_user($data, $num);

            // add/update user
            if ($user->id) {
                $DB->update_record('user', $user);
                $user->newuser = $OLD;
            } else {
                unset($user->id);
                $user->id = $DB->insert_record('user', $user);
                $user->newuser = $NEW;
            }

            // fix enrolments and grades
            $category = $this->fix_enrolments($data, $user, $time);

            // print headings (first time only)
            if ($table=='') {
                $table .= html_writer::start_tag('table', array('class' => 'users', 'border' => 1, 'cellspacing' => 4, 'cellpadding' => '4'));
                $table .= html_writer::start_tag('tr', array('class' => 'headings', 'bgcolor' => '#eebbee'));
                foreach ($columns as $column) {
                    switch (true) {
                        case ($column=='newuser'):
                            $heading = "$NEW ?";
                            break;
                        case ($column=='id'):
                            $heading = $column;
                            break;
                        case ($column=='rawpassword'):
                            $heading = get_string('password');
                            break;
                        case ($column=='courses'):
                            $heading = get_string('studentcourses', 'tool_createusers');
                            break;
                        case ($column=='groups'):
                            $heading = get_string('studentgroups', 'tool_createusers');
                            break;
                        case ($column=='category'):
                            $heading = get_string('teachercourse', 'tool_createusers');
                            break;
                        case isset($USER->$column):
                            $heading = get_string($column);
                            break;
                        default:
                            $heading = $column;
                    }
                    $table .= html_writer::tag('th', $heading, array('class' => $column));
                }
                $table .= html_writer::end_tag('tr');

                list($courses, $groups) = $this->format_courses_and_groups($data);
            }

            // print user data
            if ($i % 2) {
                $class = 'user odd';
                $bgcolor = '#eeeeaa';
            } else {
                $class = 'user even';
                $bgcolor = '#ffffee';
            }
            $table .= html_writer::start_tag('tr', array('class' => $class, 'bgcolor' => $bgcolor));
            foreach ($columns as $column) {
                if ($column=='courses') {
                    $table .= html_writer::tag('td', $courses, array('class' => $column));
                } else if ($column=='groups') {
                    $table .= html_writer::tag('td', $groups, array('class' => $column));
                } else if ($column=='category') {
                    $table .= html_writer::tag('td', $category, array('class' => $column));
                } else {
                    $table .= html_writer::tag('td', $user->$column, array('class' => $column));
                }
            }
            $table .= html_writer::end_tag('tr');
        }

        if ($table) {
            $table .= html_writer::end_tag('table');
        }

        echo preg_replace('/\s*(bgcolor|border|cellpadding|cellspacing)="[^"]*"/i', '', $table);

        // add this table as a resource to each course
        $this->add_login_resources($data, $table);
    }

    /**
     * create_user
     *
     * @param integer $data
     * @param string  $num
     */
    public function create_user($data, $num) {
        global $CFG, $DB;

        // names
        $username  = $this->create_name($data, 'username',  $num);
        $password  = $this->create_name($data, 'password',  $num, $username);
        $firstname = $this->create_name($data, 'firstname', $num, $username);
        $lastname  = $this->create_name($data, 'lastname',  $num, $username);
        $alternatename = $this->create_name($data, 'alternatename', $num, $username);

        // userid
        if ($data->usernametype==self::TYPE_USERID) {
            $userid = $DB->get_field('user', 'id', array('id' => intval($num)));
        } else {
            $userid = $DB->get_field('user', 'id', array('username' => $username));
        }

        // defaults
        $lang = $data->lang;
        if (empty($data->timezone)) {
            $timezone = 0;
        } else {
            $timezone = $data->timezone;
        }
        $calendar = $data->calendar;
        $description = $data->description;
        $mnethostid  = $CFG->mnet_localhost_id;

        return (object)array(
            'id'        => $userid,
            'username'  => $username,
            'auth'      => 'manual',
            'confirmed' => '1',
            'policyagreed' => '1',
            'deleted'   => '0',
            'suspended' => '0',
            'mnethostid' => $mnethostid,
            'password'  => md5($password),
            'rawpassword'  => $password,
            'idnumber'  => '',
            'firstname' => $firstname,
            'lastname'  => $lastname,
            'email'     => $username.'@localhost.com',
            'emailstop' => '1',
            'icq'       => '',
            'skype'     => '',
            'yahoo'     => '',
            'aim'       => '',
            'msn'       => '',
            'phone1'    => '',
            'phone2'    => '',
            'institution' => '',
            'department'  => '',
            'address'   => '',
            'city'      => '',
            'country'   => '',
            'lang'      => $lang,
            'theme'     => '',
            'timezone'  => $timezone,
            'firstaccess'   => '0',
            'lastaccess'    => '0',
            'lastlogin'     => '0',
            'currentlogin'  => '0',
            'lastip'        => '',
            'secret'        => '',
            'picture'       => '0',
            'url'           => '',
            'description'   => $description['text'],
            'descriptionformat' => $description['format'],
            'mailformat'    => '1',
            'maildigest'    => '0',
            'maildisplay'   => '2',
            'autosubscribe' => '1',
            'trackforums'   => '0',
            'timecreated'   => '0',
            'timemodified'  => '0',
            'trustbitmask'  => '0',
            'imagealt'      => '',
            'lastnamephonetic'  => '',
            'firstnamephonetic' => '',
            'middlename'    => '',
            'alternatename' => $alternatename,
            'calendartype'  => $calendar
        );
    }

    /**
     * create_name
     *
     * @param integer $data
     * @param integer $name
     * @param string  $num (id or sequence)
     * @param string  $username
     */
    public function create_name($data, $name, $num, $username='') {

        $prefix = $name.'prefix';
        if (isset($data->$prefix)) {
            $prefix = $data->$prefix;
        } else {
            $prefix = '';
        }

        $suffix = $name.'suffix';
        if (isset($data->$suffix)) {
            $suffix = $data->$suffix;
        } else {
            $suffix = '';
        }

        $type = $name.'type';
        if (isset($data->$type)) {
            $type = $data->$type;
        } else {
            $type = self::TYPE_SEQUENCE;
        }

        switch ($type) {

            case self::TYPE_FIXED:
                return $prefix.$suffix;

            case self::TYPE_SEQUENCE:
                return $prefix.$num.$suffix;

            case self::TYPE_RANDOM:
                $random = $this->create_random($data);
                return $prefix.$random.$suffix;

            case self::TYPE_USERID:
                return $prefix.$num.$suffix;

            case self::TYPE_USERNAME:
                return $prefix.$username.$suffix;


            default: return ''; // shouldn;t happen !!
        }
    }

    /**
     * create_random
     *
     * @param integer $data
     * @param integer $name
     */
    public function create_random($data) {
        $chars = array();
        for ($i=0; $i<$data->countlowercase; $i++) {
            $chars[] = array_rand($this->lowercase);
        }
        for ($i=0; $i<$data->countuppercase; $i++) {
            $chars[] = array_rand($this->uppercase);
        }
        for ($i=0; $i<$data->countnumeric; $i++) {
            $chars[] = array_rand($this->numeric);
        }
        if ($data->shufflerandom) {
            shuffle($chars);
        }
        return implode('', $chars);
    }

    /**
     * fix_enrolments
     *
     * @param integer $userid
     */
    public function fix_enrolments($data, $user, $time) {
        global $CFG, $DB;

        if ($data->resetgrades) {
            $this->reset_grades($user);
        }
        if ($data->cancelenrolments) {
            enrol_user_delete($user); // lib/enrollib.php
        }
        if ($data->cancelroles) {
            role_unassign_all(array('userid' => $user->id)); // lib/accesslib.php
            $DB->delete_records('groups_members', array('userid' => $user->id));
        }

        if (empty($data->enrolcourses)) {
            $courseids = array();
        } else if (is_array($data->enrolcourses)) {
            $courseids = $data->enrolcourses;
            $courseids = array_filter($courseids);
        } else {
            $courseids = array($data->enrolcourses);
        }

        if (empty($data->enrolgroups)) {
            $groups = array();
        } else {
            $groups = explode(',', $data->enrolgroups);
            $groups = array_map('trim', $groups);
            $groups = array_filter($groups);
        }

        foreach ($courseids as $courseid) {
            if ($role = $this->get_role_record('student')) {
                if ($context = $this->get_context(CONTEXT_COURSE, $courseid)) {
                    $this->get_role_assignment($context->id, $role->id, $user->id, $time);
                    foreach ($groups as $group) {
                        if ($groupid = $this->get_groupid($courseid, $group, $time)) {
                            $this->get_group_memberid($groupid, $user->id, $time);
                        }
                    }
                    if (method_exists($context, 'mark_dirty')) {
                        // Moodle >= 2.2
                        $context->mark_dirty();
                    } else {
                        // Moodle <= 2.1
                        mark_context_dirty($context->path);
                    }
                }
                if ($enrol = $this->get_enrol($courseid, $role->id, $user->id, $time)) {
                    $this->get_user_enrolment($enrol->id, $user->id, $time);
                }
            }
        }

        $category = '';
        if ($data->enrolcategory) {
            if ($data->doublebyte) {
                $coursename = mb_convert_kana($user->username, 'AS', 'UTF-8');
            } else {
                $coursename = $user->username;
            }
            if ($courseid = $this->get_user_courseid($data->enrolcategory, $coursename, $time)) {
                if ($context = $this->get_context(CONTEXT_COURSE, $courseid)) {

                    // enrol new $user as an "editingteacher"
                    if ($role = $this->get_role_record('editingteacher')) {
                        $this->get_role_assignment($context->id, $role->id, $user->id, $time);
                        if (method_exists($context, 'mark_dirty')) {
                            // Moodle >= 2.2
                            $context->mark_dirty();
                        } else {
                            // Moodle <= 2.1
                            mark_context_dirty($context->path);
                        }
                        if ($enrol = $this->get_enrol($courseid, $role->id, $user->id, $time)) {
                            $this->get_user_enrolment($enrol->id, $user->id, $time);
                        }
                    }

                    // enrol "student" users
                    if ($role = $this->get_role_record('student')) {
                        if (empty($data->enrolstudents)) {
                            $userids = array();
                        } else if (is_array($data->enrolstudents)) {
                            $userids = $data->enrolstudents;
                            $userids = array_filter($userids);
                        } else {
                            $userids = array($data->enrolstudents);
                        }
                        foreach ($userids as $userid) {
                            $this->get_role_assignment($context->id, $role->id, $userid, $time);
                            if (method_exists($context, 'mark_dirty')) {
                                // Moodle >= 2.2
                                $context->mark_dirty();
                            } else {
                                // Moodle <= 2.1
                                mark_context_dirty($context->path);
                            }
                            if ($enrol = $this->get_enrol($courseid, $role->id, $userid, $time)) {
                                $this->get_user_enrolment($enrol->id, $userid, $time);
                            }
                        }
                    }

                    // add course files respository
                    if ($path = preg_replace('/[\/\\\\](\.*[\/\\\\])+/', '/', $data->folderpath)) {
                        $this->get_repository_instance_id($context, $user->id, "$user->username files", $path, 1);
                    }
                }

                // remove all labels, resources and activities
                if ($data->resetcourses) {
                    if ($cms = $DB->get_records('course_modules', array('course' => $courseid), 'id,course')) {
                        foreach ($cms as $cm) {
                            $this->remove_coursemodule($cm->id);
                        }
                    }
                }


                // format link to course
                $url = new moodle_url('/course/view.php', array('id' => $courseid));
                $category = html_writer::link($url, $coursename, array('target' => '_blank'));
            }
        }
        return $category;
    }

    /**
     * get_context
     *
     * a wrapper method to offer consistent API to get contexts
     * in Moodle 2.0 and 2.1, we use context() function
     * in Moodle >= 2.2, we use static context_xxx::instance() method
     *
     * @param integer $contextlevel
     * @param integer $instanceid (optional, default=0)
     * @param int $strictness (optional, default=0 i.e. IGNORE_MISSING)
     * @return required context
     * @todo Finish documenting this function
     */
    public static function get_context($contextlevel, $instanceid=0, $strictness=0) {
        if (class_exists('context_helper')) {
            // use call_user_func() to prevent syntax error in PHP 5.2.x
            // return $classname::instance($instanceid, $strictness);
            $class = context_helper::get_class_for_level($contextlevel);
            return call_user_func(array($class, 'instance'), $instanceid, $strictness);
        } else {
            return get_context_instance($contextlevel, $instanceid);
        }
    }

    /**
     * get_userfields
     *
     * @param string $tableprefix name of database table prefix in query
     * @param array  $extrafields extra fields to be included in result (do not include TEXT columns because it would break SELECT DISTINCT in MSSQL and ORACLE)
     * @param string $idalias     alias of id field
     * @param string $fieldprefix prefix to add to all columns in their aliases, does not apply to 'id'
     * @return string
     */
     function get_userfields($tableprefix = '', array $extrafields = NULL, $idalias = 'id', $fieldprefix = '') {
        if (class_exists('user_picture')) { // Moodle >= 2.6
            return user_picture::fields($tableprefix, $extrafields, $idalias, $fieldprefix);
        }
        // Moodle <= 2.5
        $fields = array('id', 'firstname', 'lastname', 'picture', 'imagealt', 'email');
        if ($tableprefix || $extrafields || $idalias) {
            if ($tableprefix) {
                $tableprefix .= '.';
            }
            if ($extrafields) {
                $fields = array_unique(array_merge($fields, $extrafields));
            }
            if ($idalias) {
                $idalias = " AS $idalias";
            }
            if ($fieldprefix) {
                $fieldprefix = " AS $fieldprefix";
            }
            foreach ($fields as $i => $field) {
                $fields[$i] = "$tableprefix$field".($field=='id' ? $idalias : ($fieldprefix=='' ? '' : "$fieldprefix$field"));
            }
        }
        return implode(',', $fields);
        //return 'u.id AS userid, u.username, u.firstname, u.lastname, u.picture, u.imagealt, u.email';
    }

    /**
     * get_role_record
     *
     * @param string $name
     * @return object or boolean (FALSE)
     */
    public function get_role_record($name) {
        global $DB;

        if ($role = $DB->get_record('role', array('shortname' => $name))) {
            return $role;
        }

        // create new $role record for this $name
        if ($sortorder = $DB->get_field('role', 'MAX(sortorder)', array())) {
            $sortorder ++;
        } else {
            $sortorder = 1;
        }
        $role = (object)array(
            'name'        => $name,
            'shortname'   => $name,
            'description' => $name,
            'sortorder'   => $sortorder,
            'archetype'   => $name
        );

        if ($role->id = $DB->insert_record('role', $role)) {
            return $role;
        }

        // could not create role record !!
        return false;
    }

    /**
     * get_enrol
     *
     * @param integer $courseid
     * @param integer $roleid
     * @param integer $userid modifierid for new enrol record
     * @param integer $time
     * @return object or boolean (FALSE)
     */
    public function get_enrol($courseid, $roleid, $userid, $time) {
        global $DB;
        $params = array('enrol' => 'manual', 'courseid' => $courseid, 'roleid' => $roleid);
        if ($record = $DB->get_record('enrol', $params)) {
            return $record;
        }
        $record = (object)array(
            'enrol'        => 'manual',
            'courseid'     => $courseid,
            'roleid'       => $roleid,
            'modifierid'   => $userid,
            'timecreated'  => $time,
            'timemodified' => $time
        );
        if ($record->id = $DB->insert_record('enrol', $record)) {
            return $record;
        }
        return false;
    }

    /**
     * get_role_assignment
     *
     * @param integer $contextid
     * @param integer $roleid
     * @param integer $userid to be assigned a role
     * @param integer $time
     * @return boolean TRUE  if a new role_assignment was created, FALSE otherwise
     */
    public function get_role_assignment($contextid, $roleid, $userid, $time) {
        global $DB, $USER;
        $params = array('roleid' => $roleid, 'contextid' => $contextid, 'userid' => $userid);
        if ($record = $DB->get_record('role_assignments', $params)) {
            return $record;
        }
        $record = (object)array(
            'roleid'       => $roleid,
            'contextid'    => $contextid,
            'userid'       => $userid,
            'modifierid'   => $USER->id,
            'timemodified' => $time
        );
        if ($record->id = $DB->insert_record('role_assignments', $record)) {
            return $record;
        }
        return false; // shouldn't happen !!
    }

    /**
     * get_user_enrolment
     *
     * @param integer $enrolid
     * @param integer $userid to be enrolled
     * @param integer $time
     * @return boolean TRUE if a new role_assignment was created, FALSE otherwise
     */
    public function get_user_enrolment($enrolid, $userid, $time) {
        global $DB, $USER;
        $params = array('enrolid' => $enrolid, 'userid' => $userid);
        if ($record = $DB->get_record('user_enrolments', $params)) {
            $record->timestart = $time;
            $record->timeend = 0;
            if ($DB->update_record('user_enrolments', $record)) {
                return $record;
            }
        } else {
            $record = (object)array(
                'enrolid'      => $enrolid,
                'userid'       => $userid,
                'modifierid'   => $USER->id,
                'timestart'    => $time,
                'timeend'      => 0,
                'timecreated'  => $time,
                'timemodified' => $time
            );
            if ($record->id = $DB->insert_record('user_enrolments', $params)) {
                return $record;
            }
        }
        return false;
    }

    /**
     * get_groupid
     *
     * @param integer $courseid
     * @param string  $name
     * @param integer $time
     * @return integer id of group record if one exists, FALSE otherwise
     */
    public function get_groupid($courseid, $name, $time) {
        global $DB;
        if ($id = $DB->get_field('groups', 'id', array('courseid' => $courseid, 'name' => $name))) {
            return $id;
        }
        // add new group for this course
        $group = (object)array(
            'courseid'     => $courseid,
            'name'         => $name,
            'description'  => '',
            'descriptionformat' => FORMAT_MOODLE,
            'enrolmentkey' => '',
            'timecreated'  => $time,
            'timemodified' => $time
        );
        return $DB->insert_record('groups', $group);
    }

    /**
     * get_group_memberid
     *
     * @param integer $groupid
     * @param integer $userid
     * @param integer $time
     * @return boolean TRUE  if a new group was created, FALSE otherwise
     */
    public function get_group_memberid($groupid, $userid, $time) {
        global $DB;
        if ($id = $DB->get_field('groups_members', 'id', array('groupid' => $groupid, 'userid' => $userid))) {
            return $id;
        }
        // add new member for this group
        $member = (object)array(
            'groupid'  => $groupid,
            'userid'   => $userid,
            'timeadded' => $time
        );
        return $DB->insert_record('groups_members', $member);
    }

    /**
     * reset_grades
     *
     * @param object $user
     * @return void
     */
    public function reset_grades($user) {
        global $DB;

        // get $user's grades
        if (! $grades = $DB->get_records_menu('grade_grades', array('userid' => $user->id), null, 'id,itemid')) {
            return false;
        }

        // remove all $user's grades
        list($select, $params) = $DB->get_in_or_equal(array_keys($grades));
        $DB->delete_records_select('grade_grades', "id $select", $params);

        // select all "mod" grade items for this user
        list($select, $params) = $DB->get_in_or_equal(array_values($grades));
        $select .= ' AND itemtype = ?';
        $params[] = 'mod';
        if (! $items = $DB->get_records_select('grade_items', "id $select", $params)) {
            return false;
        }

        // remove $user's grade for each grade item
        foreach ($items as $item) {
            if (! $mod = $item->itemmodule) {
                continue; // empty module name ?!
            }
            $params = array('id' => $item->iteminstance);
            if (! $instance = $DB->get_record($mod, $params)) {
                continue; // invalid instance id ?!
            }
            $params = array('module' => $mod, 'instance' => $instance->id);
            if (! $cm = $DB->get_record('course_modules', $params)) {
                continue; // no course_module ?!
            }

            // fields required by "xxx_update_grades"
            $instance->cmidnumber = $cm->idnumber;
            $instance->courseid   = $cm->course;

            $method = 'reset_grades_'.$mod;
            if (method_exists($this, $method)) {
                $this->$method($instance, $user);
            } else {
                // remove any info about this user in this mod's tables
                $this->reset_grades_mod($mod, $instance, $user);
            }
        }
    }

    /**
     * reset_grades_mod
     *
     * @param object $mod
     * @param object $instance
     * @param object $user
     * @return void
     */
    public function reset_grades_mod($mod, $instance, $user) {
        global $CFG, $DB;

        if (! $tables = $DB->get_tables() ) {
            return false; // shoudln't happen !!
        }

        foreach ($tables as $table) {
            if (strpos($table, $mod.'_')===0) {
                if ($columns = $DB->get_columns($table)) {
                    if (array_key_exists('userid', $columns)) {
                        $params = array('userid' => $user->id);
                        $DB->delete_records($table, $params);
                    }
                }
            }
        }

        $file = $CFG->dirroot.'/mod/$mod/lib.php';
        if (file_exists($file)) {
            require_once($file);
            $function = $mod.'_update_grades';
            if (function_exists($function)) {
                $function($instance, $user->id);
            }
        }
    }

    /**
     * reset_grades_assignment
     *
     * @param object $instance
     * @param object $user
     * @return void
     */
    public function reset_grades_assignment($instance, $user) {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/mod/assignment/lib.php');

        // remove assignment submissions and grades
        $select = 'assignment = ? AND userid =?';
        $params = array($instance->id, $user->id);
        $DB->delete_records_select('assignment_submissions', $select, $params);
        assignment_update_grades($instance, $user->id);
    }

    /**
     * reset_grades_quiz
     *
     * @param object $instance
     * @param object $user
     * @return void
     */
    public function reset_grades_quiz($instance, $user) {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/mod/quiz/lib.php');
        require_once($CFG->dirroot.'/lib/questionlib.php');

        // delete question attempts
        $from    = '{quiz_attempts} quiza JOIN {quiz} quiz ON quiza.quiz = quiz.id';
        $usageid = 'quiza.uniqueid';
        $where   = 'quiz.id = :quizid AND quiza.userid = :userid';
        $params  = array('quizid' => $instance->id, 'userid' => $user->id);
        question_engine::delete_questions_usage_by_activities(new qubaid_join($from, $usageid, $where, $params));

        // remove quiz attempts and grades
        $select = 'quiz = ? AND userid =?';
        $params = array($instance->id, $user->id);
        $DB->delete_records_select('quiz_attempts', $select, $params);
        $DB->delete_records_select('quiz_grades',   $select, $params);
        quiz_update_grades($instance, $user->id);
    }

    /**
     * format_courses_and_groups
     *
     * @param  object $data
     * @return array(string $courses, string $groups)
     */
    public function format_courses_and_groups($data) {
        global $DB;

        if (empty($data->enrolcourses)) {
            return array('', '');
        }

        $courses = $data->enrolcourses;

        if (! is_array($courses)) {
            $courses = explode(',', $courses);
            $courses = array_filter($courses);
        }

        list($courseselect, $courseparams) = $DB->get_in_or_equal($courses);

        if ($courses = $DB->get_records_select_menu('course', "id $courseselect", $courseparams, 'shortname', 'id,shortname')) {
            foreach ($courses as $id => $name) {
                $url = new moodle_url('/course/view.php', array('id' => $id));
                $courses[$id] = html_writer::link($url, $name, array('target' => '_blank'));
            }
            $courses = implode(', ', $courses);
        } else {
            $courses = ''; // shouldn't happen !!
        }

        if (empty($data->enrolgroups)) {
            return array($courses, '');
        }

        $groups = $data->enrolgroups;

        if (! is_array($groups)) {
            $groups = explode(',', $groups);
            $groups = array_map('trim', $groups);
            $groups = array_filter($groups);
        }

        list($groupselect, $groupparams) = $DB->get_in_or_equal($groups);

        $select = "courseid $courseselect AND name $groupselect";
        $params = array_merge($courseparams, $groupparams);
        if ($groups = $DB->get_records_select('groups', $select, $params, 'name', 'id,courseid,name')) {
            foreach ($groups as $id => $group) {
                $params = array('id' => $group->courseid, 'group' => $id);
                $url = new moodle_url('/group/index.php', $params);
                $groups[$id] = html_writer::link($url, $group->name, array('target' => '_blank'));
            }
            $groups = implode(', ', $groups);
        } else {
            $groups = ''; // shouldn't happen !!
        }

        return array($courses, $groups);
    }

    /**
     * add_login_resources
     *
     * @param object $data
     * @param string $table
     */
    public function add_login_resources($data, $table) {
        global $DB;

        if (empty($data->enrolcourses)) {
            return false;
        }

        $courses = $data->enrolcourses;

        if (! is_array($courses)) {
            $courses = explode(',', $courses);
            $courses = array_filter($courses);
        }

        list($select, $params) = $DB->get_in_or_equal($courses);

        if (empty($data->enrolgroups)) {
            $groups = array();
        } else {
            $groups = $data->enrolgroups;
            if (! is_array($groups)) {
                $groups = explode(',', $groups);
                $groups = array_map('trim', $groups);
                $groups = array_filter($groups);
            }
        }

        $links = '';
        if ($courses = $DB->get_records_select('course', "id $select", $params, 'id', 'id,shortname')) {
            foreach ($courses as $course) {
                if (empty($groups)) {
                    if ($cm = $this->add_login_resource($course->id, $table)) {
                        $url = new moodle_url('/mod/page/view.php', array('id' => $cm->id));
                        $link = html_writer::link($url, $cm->name, array('target' => '_blank'));
                        $links .= html_writer::tag('li', $link);
                    }
                } else {
                    foreach ($groups as $group) {
                        if ($cm = $this->add_login_resource($course->id, $table, $group)) {
                            $url = new moodle_url('/mod/page/view.php', array('id' => $cm->id));
                            $link = html_writer::link($url, $cm->name, array('target' => '_blank'));
                            $links .= html_writer::tag('li', $link);
                        }
                    }
                }
            }
        }
        if ($links) {
            echo html_writer::tag('ul', $links, array('class' => 'loginresources'));
        }
    }

    /**
     * add_login_resource
     *
     * @param  object  $course
     * @param  string  $table
     * @return object  $cm course_module record of newly added/updated page resource
     */
    public function add_login_resource($courseid, $table, $group='', $sectionnum=0) {
        global $DB, $USER;

        static $pagemoduleid = null;
        if ($pagemoduleid===null) {
            $pagemoduleid = $DB->get_field('modules', 'id', array('name' => 'page'));
        }

        if ($group=='') {
            $name = get_string('userlogindetails', 'tool_createusers');
        } else {
            $name = get_string('userlogindetailsgroup', 'tool_createusers', $group);
        }

        $select = 'cm.*, ? AS modulename, p.name AS name';
        $from   = '{course_modules} cm '.
                  'JOIN {page} p ON cm.module = ? AND cm.instance = p.id';
        $where  = 'p.course = ? AND p.name = ?';
        $params = array('page', $pagemoduleid, $courseid, $name);
        $order  = 'cm.visible DESC, cm.added DESC'; // newest, visible cm first

        if ($cm = $DB->get_records_sql("SELECT $select FROM $from WHERE $where ORDER BY $order", $params, 0, 1)) {
            $cm  = reset($cm);
            $cm->content = $table;
            $DB->set_field('page', 'content', $table, array('id' => $cm->instance));
        } else {
            $cm = (object)array(
                // standard page resource fields
                'name'            => $name,
                'intro'           => ' ',
                'introformat'     => FORMAT_HTML,
                'content'         => $table,
                'contentformat'   => FORMAT_HTML,
                'tobemigrated'    => 0,
                'legacyfiles'     => 0,
                'legacyfileslast' => 0,
                'display'         => 0,
                'displayoptions'  => '',
                'revision'        => 0,
                'timemodified'    => time(),

                // standard fields for adding a new cm
                'course'          => $courseid,
                'section'         => $sectionnum,
                'module'          => $pagemoduleid,
                'modulename'      => 'page',
                'add'             => 'page',
                'update'          => 0,
                'return'          => 0,
                'cmidnumber'      => '',
                'visible'         => 0,
                'groupmode'       => 0,
                'MAX_FILE_SIZE'   => 0,
            );

            if (! $cm->instance = $DB->insert_record('page', $cm)) {
                return false;
            }
            if (! $cm->id = add_course_module($cm) ) { // $mod
                throw new moodle_exception('Could not add a new course module');
            }
            $cm->coursemodule = $cm->id;
            if (function_exists('course_add_cm_to_section')) {
                $sectionid = course_add_cm_to_section($courseid, $cm->id, $sectionnum);
            } else {
                $sectionid = add_mod_to_section($cm);
            }
            if ($sectionid===false) {
                throw new moodle_exception('Could not add new course module to section: '.$sectionnum);
            }
            if (! $DB->set_field('course_modules', 'section',  $sectionid, array('id' => $cm->id))) {
                throw new moodle_exception('Could not update the course module with the correct section');
            }

            // if the section is hidden, we should also hide the new quiz activity
            if (! isset($cm->visible)) {
                $cm->visible = $DB->get_field('course_sections', 'visible', array('id' => $sectionid));
            }
            set_coursemodule_visible($cm->id, $cm->visible);
        }

        // Trigger mod_updated event with information about this module.
        $event = (object)array(
            'cmid'       => $cm->id,
            'courseid'   => $cm->course,
            'modulename' => $cm->modulename,
            'name'       => $cm->name,
            'userid'     => $USER->id
        );
        if (function_exists('events_trigger_legacy')) {
            events_trigger_legacy('mod_updated', $event);
        } else {
            events_trigger('mod_updated', $event);
        }

        // rebuild_course_cache (needed for Moodle 2.0)
        rebuild_course_cache($courseid, true);

        return $cm;
    }

    /**
     * get_user_courseid
     *
     * @param integer $categoryid
     * @param string  $coursename
     * @param integer $time
     * @return mixed return id if a course was located/created, FALSE otherwise
     */
    public function get_user_courseid($categoryid, $coursename, $time, $numsections=3, $format='topics') {
        global $CFG, $DB;

        if ($course = $DB->get_record('course', array('shortname' => $coursename))) {
            $DB->set_field('course', 'category', $categoryid, array('id' => $course->id));
            return $course->id;
        }

        // create new course
        $course = (object)array(
            'category'      => $categoryid, // crucial !!
            'fullname'      => $coursename,
            'shortname'     => $coursename,
            'summary'       => '',
            'summaryformat' => FORMAT_PLAIN, // plain text
            'format'        => $format,
            'newsitems'     => 0,
            'startdate'     => $time,
            'visible'       => 1, // visible
            'numsections'   => $numsections
        );

        // create course (with no blocks)
        $CFG->defaultblocks_override = ' ';
        $course = create_course($course);

        if ($sortorder = $DB->get_field('course', 'MAX(sortorder)', array())) {
            $sortorder ++;
        } else {
            $sortorder = 100;
        }
        $DB->set_field('course', 'sortorder', $sortorder, array('id' => $course->id));

        if (empty($course)) {
            return false;
        } else {
            return $course->id;
        }
    }

    /**
     * get_course_categoryid
     *
     * @param string  $categoryname
     * @param integer $parentcategoryid
     * @return mixed return id if a course category was located/created, FALSE otherwise
     */
    public function get_course_categoryid($categoryname, $parentcategoryid) {
        global $CFG, $DB;

        $select = 'name = ? AND parent = ?';
        $params = array($categoryname, $parentcategoryid);
        if ($category = $DB->get_records_select('course_categories', $select, $params)) {
            $category = reset($category); // in case there are duplicates
            return $category->id;
        }

        // create new category
        $category = (object)array(
            'name'         => $categoryname,
            'parent'       => $parentcategoryid,
            'depth'        => 1,
            'sortorder'    => 0,
            'timemodified' => time()
        );
        if (class_exists('coursecat')) {
            // Moodle >= 2.5
            $category = coursecat::create($category);
        } else {
            // Moodle <= 2.4
            if ($category->id = $DB->insert_record('course_categories', $category)) {
                fix_course_sortorder(); // Required to build course_categories.depth and .path.
                mark_context_dirty(get_context_instance(CONTEXT_COURSECAT, $category->id));
            }
        }

        if (empty($category)) {
            return false;
        } else {
            return $category->id;
        }
    }

    /**
     * get_moodledata_folders
     */
    public function get_moodledata_folders($path) {
        global $CFG;
        $folders = array();
        $dir = $CFG->dataroot.'/'.$path;
        if (is_dir($dir) && ($fh = opendir($dir))) {
            while ($item = readdir($fh)) {
                if (substr($item, 0, 1)=='.') {
                    continue;
                }
                if (is_dir($dir.'/'.$item)) {
                    $folders[$item] = $item;
                    $fieldname = '';
                }
            }
            closedir($fh);
        }
        return $folders;
    }

    /**
     * get_repository_instance_id
     *
     * @param object   $context
     * @param integer  $userid
     * @param string   $name
     * @param string   $path
     * @param integer  $relativefiles
     * @return integer id from repository_instances table
     */
    public function get_repository_instance_id($context, $userid, $name, $path, $relativefiles) {
        $type = 'filesystem';
        $params = array('type' => $type, 'currentcontext' => $context, 'context' => array($context), 'userid' => $userid);
        if ($instances = repository::get_instances($params)) {
            foreach ($instances as $instance) {
                if ($instance->get_option('fs_path')==$path) {
                    $params = array('name' => $name, 'fs_path' => $path, 'relativefiles' => $relativefiles);
                    $instance->set_option($params);
                    return $instance->id;
                }
            }
        }
        $params = array('name' => $name, 'fs_path' => $path, 'relativefiles' => $relativefiles);
        return repository::static_function($type, 'create', $type, $userid, $context, $params);
    }

    /**
     * remove_coursemodule
     *
     * @param integer  $cmid
     * @return void, but may update Moodle database
     */
    public function remove_coursemodule($cmid) {
        global $CFG, $DB;

        if (function_exists('course_delete_module')) {
            // Moodle >= 2.5
            course_delete_module($cmid);
        } else {
            // Moodle <= 2.4
            $cm = get_coursemodule_from_id('', $cmid, 0, true);

            $libfile = $CFG->dirroot.'/mod/'.$cm->modname.'/lib.php';
            if (! file_exists($libfile)) {
                throw new moodle_exception("$cm->modname lib.php not accessible ($libfile)");
            }
            require_once($libfile);

            $deleteinstancefunction = $cm->modname.'_delete_instance';
            if (! function_exists($deleteinstancefunction)) {
                throw new moodle_exception("$cm->modname delete function not found ($deleteinstancefunction)");
            }

            // copied from 'course/mod.php'
            if (! $deleteinstancefunction($cm->instance)) {
                throw new moodle_exception("Could not delete the $cm->modname (instance id=$cm->instance)");
            }
            if (! delete_course_module($cm->id)) {
                throw new moodle_exception("Could not delete the $cm->modname (coursemodule, id=$cm->id)");
            }
            if (! $sectionid = $DB->get_field('course_sections', 'id', array('course' => $cm->course, 'section' => $cm->sectionnum))) {
                throw new moodle_exception("Could not get section id (course id=$cm->course, section num=$cm->sectionnum)");
            }
            if (! delete_mod_from_section($cm->id, $sectionid)) {
                throw new moodle_exception("Could not delete the $cm->modname (id=$cm->id) from that section (id=$sectionid)");
            }
        }
    }
}
