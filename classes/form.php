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
 * Site wide search-createusers form.
 *
 * @package    tool_createusers
 * @copyright  2013 Gordon Bateson {@link http://quizport.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Site wide search-createusers form.
 */
class tool_createusers_form extends moodleform {

    var $numeric     = null;
    var $lowercase   = null;
    var $uppercase   = null;

    const TYPE_FIXED     = 1;
    const TYPE_RANDOM    = 2;
    const TYPE_SEQUENCE  = 3;
    const TYPE_USERID    = 4;
    const TYPE_USERNAME  = 5;

    const SIZE_INT  = 5;
    const SIZE_TEXT = 10;

    /**
     * constructor
     */
    function tool_createusers_form($action=null, $customdata=null, $method='post', $target='', $attributes=null, $editable=true) {
        $this->numeric   = array_flip(str_split('23456789', 1));
        $this->lowercase = array_flip(str_split('abdeghjmnpqrstuvyz', 1));
        $this->uppercase = array_flip(str_split('ABDEGHJLMNPQRSTUVWXYZ', 1));
        parent::moodleform($action, $customdata, $method, $target, $attributes, $editable);
    }

    /**
     * definition
     */
    function definition() {
        global $CFG, $DB;

        $mform = $this->_form;
        $tool = 'tool_createusers';
        $dot = get_string('stringseparator', $tool);

        //==========================
        // usernames
        //==========================
        //
        $name = 'usernames';
        $mform->addElement('header', $name, get_string($name, $tool));
        if (method_exists($mform, 'setExpanded')) {
            $mform->setExpanded($name, true);
        }

        // number of users
        $name = 'countusers';
        $mform->addElement('text', $name, get_string($name, $tool), array('size' => self::SIZE_INT));
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 20);

        // username prefix
        $name = 'usernameprefix';
        $mform->addElement('text', $name, get_string('prefix', $tool), array('size' => self::SIZE_TEXT));
        $mform->setType($name, PARAM_TEXT);
        $mform->setDefault($name, get_string('default'.$name, $tool).$dot);

        // username numeric type
        $name = 'usernametype';
        $types = array(self::TYPE_USERID   => get_string('typeuserid', $tool),
                       self::TYPE_SEQUENCE => get_string('typesequence',   $tool));
        $mform->addElement('select', $name, get_string($name, $tool), $types);
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, self::TYPE_SEQUENCE);

        // username numeric width
        $name = 'usernamewidth';
        $width = array_combine(range(1, 8), range(1, 8));
        $mform->addElement('select', $name, get_string($name, $tool), $width);
        $mform->setType($name, PARAM_TEXT);
        $mform->setDefault($name, 3);

        // start users
        $name = 'startusers';
        $mform->addElement('text', $name, get_string($name, $tool), array('size' => self::SIZE_INT));
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 1);

        // increment users
        $name = 'incrementusers';
        $mform->addElement('text', $name, get_string($name, $tool), array('size' => self::SIZE_INT));
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 1);

        // username suffix
        $name = 'usernamesuffix';
        $mform->addElement('text', $name, get_string('suffix', $tool), array('size' => self::SIZE_TEXT));
        $mform->setType($name, PARAM_TEXT);
        $mform->setDefault($name, '');

        //==========================
        // passwords
        //==========================
        //
        $name = 'passwords';
        $mform->addElement('header', $name, get_string($name, $tool));
        if (method_exists($mform, 'setExpanded')) {
            $mform->setExpanded($name, true);
        }

        // password type
        $name = 'passwordtype';
        $types = array(self::TYPE_FIXED    => get_string('typefixed',    $tool),
                       self::TYPE_USERNAME => get_string('typeusername', $tool),
                       self::TYPE_RANDOM   => get_string('typerandom',   $tool));
        $mform->addElement('select', $name, get_string($name, $tool), $types);
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, self::TYPE_RANDOM);

        // password prefix
        $name = 'passwordprefix';
        $mform->addElement('text', $name, get_string('prefix', $tool), array('size' => self::SIZE_TEXT));
        $mform->setType($name, PARAM_TEXT);
        $mform->setDefault($name, array_rand($this->lowercase).$dot);

        // num of lowercase
        $name = 'countlowercase';
        $mform->addElement('select', $name, get_string($name, $tool), range(0,8));
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 0);

        // num of uppercase
        $name = 'countuppercase';
        $mform->addElement('select', $name, get_string($name, $tool), range(0,8));
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 0);

        // num of numeric
        $name = 'countnumeric';
        $mform->addElement('select', $name, get_string($name, $tool), range(0,8));
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 4);

        // shuffle random chars
        $name = 'shufflerandom';
        $mform->addElement('selectyesno', $name, get_string($name, $tool));
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 1);

        // password suffix
        $name = 'passwordsuffix';
        $mform->addElement('text', $name, get_string('suffix', $tool), array('size' => self::SIZE_TEXT));
        $mform->setType($name, PARAM_TEXT);
        $mform->setDefault($name, '');

        //==========================
        // names
        //==========================
        //
        $name = 'names';
        $mform->addElement('header', $name, get_string($name, $tool));
        if (method_exists($mform, 'setExpanded')) {
            $mform->setExpanded($name, true);
        }

        $types = array(self::TYPE_USERNAME => get_string('typeusername', $tool),
                       self::TYPE_FIXED    => get_string('typefixed',    $tool),
                       self::TYPE_SEQUENCE => get_string('typesequence', $tool),
                       self::TYPE_RANDOM   => get_string('typerandom',   $tool));

        $names = array('firstname', 'lastname', 'alternatename');
        foreach ($names as $name) {

            // type
            $type = $name.'type';
            $mform->addElement('select', $type, get_string($type, $tool), $types);
            $mform->setType($type, PARAM_INT);
            $mform->setDefault($type, self::TYPE_SEQUENCE);

            // prefix
            $prefix = $name.'prefix';
            $mform->addElement('text', $prefix, get_string('prefix', $tool), array('size' => self::SIZE_TEXT));
            $mform->setType($prefix, PARAM_TEXT);
            $mform->setDefault($prefix, get_string('default'.$name, $tool).$dot);

            // suffix
            $suffix = $name.'suffix';
            $mform->addElement('text', $suffix, get_string('suffix', $tool), array('size' => self::SIZE_TEXT));
            $mform->setType($suffix, PARAM_TEXT);
            $mform->setDefault($suffix, '');
        }

        //==========================
        // grades and enrolments
        //==========================
        //
        $name = 'gradesandenrolments';
        $mform->addElement('header', $name, get_string($name, $tool));
        if (method_exists($mform, 'setExpanded')) {
            $mform->setExpanded($name, true);
        }

        // reset grades
        $name = 'resetgrades';
        $mform->addElement('selectyesno', $name, get_string($name, $tool));
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 1);

        // cancel role assignments
        $name = 'cancelroles';
        $mform->addElement('selectyesno', $name, get_string($name, $tool));
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 1);

        // cancel current enrolments
        $name = 'cancelenrolments';
        $mform->addElement('selectyesno', $name, get_string($name, $tool));
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 1);

        // enrol in the following courses
        $name = 'newenrolments';
        $courses = $DB->get_records_menu('course', null, 'shortname', 'id,shortname');
        $params = array('multiple' => 'multiple', 'size' => min(count($courses), 5));
        $mform->addElement('select', $name, get_string($name, $tool), $courses, $params);
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, 0);

        //==========================
        // defaults
        // (see user/editlib.php)
        //==========================
        //
        $name = 'defaults';
        $mform->addElement('header', $name, get_string($name, $tool));
        if (method_exists($mform, 'setExpanded')) {
            $mform->setExpanded($name, true);
        }

        // timezone
        $name = 'timezone';
        $default = '99';
        $zones = get_list_of_timezones();
        $zones[$default] = get_string('serverlocaltime');
        if (empty($CFG->forcetimezone) || $CFG->forcetimezone==$default) {
            $mform->addElement('select', $name, get_string($name), $zones);
            $mform->setDefault($name, $default);
        } else {
            $mform->addElement('static', 'forcedtimezone', get_string($name), $zones[$CFG->forcetimezone]);
        }

        // lang
        $mform->addElement('select', 'lang', get_string('preferredlanguage'), get_string_manager()->get_list_of_translations());
        $mform->setDefault('lang', $CFG->lang);

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
            $mform->addElement('select', $name, get_string('preferredcalendar', 'calendar'), $types);
            $mform->setDefault($name, $CFG->calendartype);
            $mform->setType($name, PARAM_ALPHA);
        } else {
            $mform->addElement('hidden', $name, (empty($CFG->calendartype) ? '' : $CFG->calendartype));
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

        //==========================
        // action buttons
        //==========================
        //
        $this->add_action_buttons(true, get_string('go'));
    }

    /**
     * validation
     *
     * @param array $data
     * @param array $files
     */
    function validation($data, $files) {
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
     * create_users
     */
    function create_users() {
        global $DB, $USER;

        // get form data
        $data = $this->get_data();
        $time = time();

        $OLD = '';
        $NEW = get_string('new');

        $printed_headings = false;
        $columns = array('newuser', 'id', 'username', 'password', 'firstname', 'lastname', 'alternatename');

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
        echo html_writer::start_tag('table', array('class' => 'users'));
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
            $this->fix_enrolments($data, $user, $time);

            // print headings (first time only)
            if ($printed_headings==false) {
                $printed_headings = true;
                echo html_writer::start_tag('tr', array('class' => 'headings'));
                foreach ($columns as $column) {
                    if ($column=='id' || ! isset($USER->$column)) {
                        $heading = $column;
                    } else {
                        $heading = get_string($column);
                    }
                    echo html_writer::tag('th', $heading, array('class' => $column));
                }
                echo html_writer::end_tag('tr');
            }

            // print user data
            $class = 'user '.(($i % 2) ? 'odd' : 'even');
            echo html_writer::start_tag('tr', array('class' => $class));
            foreach ($columns as $column) {
                echo html_writer::tag('td', $user->$column, array('class' => $column));
            }
            echo html_writer::end_tag('tr');
        }
        echo html_writer::end_tag('table');
    }

    /**
     * create_user
     *
     * @param integer $data
     * @param string  $num
     */
    function create_user($data, $num) {
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
        $timezone = $data->timezone;
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
            'password'  => $password,
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
    function create_name($data, $name, $num, $username='') {

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
    function create_random($data) {
        $chars = array();
        for ($i=0; $i<$data->countnumeric; $i++) {
            $chars[] = array_rand($this->numeric);
        }
        for ($i=0; $i<$data->countlowercase; $i++) {
            $chars[] = array_rand($this->lowercase);
        }
        for ($i=0; $i<$data->countuppercase; $i++) {
            $chars[] = array_rand($this->uppercase);
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
    function fix_enrolments($data, $user, $time) {
        global $DB;

        if ($data->resetgrades) {
            $this->reset_grades($user);
        }
        if ($data->cancelenrolments) {
            enrol_user_delete($user); // lib/enrollib.php
        }
        if ($data->cancelroles) {
            role_unassign_all(array('userid' => $user->id)); // lib/accesslib.php
        }
        if ($role = $this->get_role_record('student')) {
            foreach ($data->newenrolments as $courseid) {
                if ($context = $this->get_context(CONTEXT_COURSE, $courseid)) {
                    $this->create_role_assignment($context->id, $role->id, $user->id, $time);
                }
                if ($enrol = $this->get_enrol_record($courseid, $role->id, $user->id, $time)) {
                    $this->create_user_enrolment($enrol->id, $user->id, $time);
                }
            }
        }
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

    /*
     * get_role_record
     *
     * @param string $name
     * @return object or boolean (FALSE)
     */
    function get_role_record($name) {
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

    /*
     * get_enrol_record
     *
     * @param integer $courseid
     * @param integer $roleid
     * @param integer $userid modifierid for new enrol record
     * @param integer $time
     * @return object or boolean (FALSE)
     */
    function get_enrol_record($courseid, $roleid, $userid, $time) {
        global $DB;

        if ($enrol = $DB->get_record('enrol', array('enrol' => 'manual', 'courseid' => $courseid, 'roleid' => $roleid))) {
            return $enrol;
        }

        // create new $enrol record for $roleid in this $course
        $enrol = (object)array(
            'enrol'        => 'manual',
            'courseid'     => $courseid,
            'roleid'       => $roleid,
            'modifierid'   => $userid,
            'timecreated'  => $time,
            'timemodified' => $time
        );

        if ($enrol->id = $DB->insert_record('enrol', $enrol)) {
            return $enrol;
        }

        // could not create enrol record !!
        return false;
    }

    /*
     * create_role_assignment
     *
     * @param integer $contextid
     * @param integer $roleid
     * @param integer $userid to be assigned a role
     * @param integer $time
     * @return boolean TRUE  if a new role_assignment was created, FALSE otherwise
     */
    function create_role_assignment($contextid, $roleid, $userid, $time) {
        global $DB, $USER;
        $params = array('roleid' => $roleid, 'contextid' => $contextid, 'userid' => $userid);
        if ($DB->record_exists('role_assignments', $params)) {
            return false;
        } else {
            // add new role for user in this course
            $params['modifierid'] = $USER->id;
            $params['timemodified'] = $time;
            return $DB->insert_record('role_assignments', $params, false);
        }
    }

    /*
     * create_user_enrolment
     *
     * @param integer $enrolid
     * @param integer $userid to be enrolled
     * @param integer $time
     * @return boolean TRUE if a new role_assignment was created, FALSE otherwise
     */
    function create_user_enrolment($enrolid, $userid, $time) {
        global $DB, $USER;
        $params = array('enrolid' => $enrolid, 'userid' => $userid);
        if ($DB->record_exists('user_enrolments', $params)) {
            $DB->set_field('user_enrolments', 'timestart', $time, $params);
            $DB->set_field('user_enrolments', 'timeend', 0, $params);
            return false;
        } else {
            // enrol user in this course
            $params['modifierid'] = $USER->id;
            $params['timestart'] = $time;
            $params['timeend'] = 0;
            $params['timecreated'] = $time;
            $params['timemodified'] = $time;
            return $DB->insert_record('user_enrolments', $params, false);
        }
    }

    /*
     * reset_grades
     *
     * @param object $user
     * @return void
     */
    function reset_grades($user) {
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

    /*
     * reset_grades_mod
     *
     * @param object $mod
     * @param object $instance
     * @param object $user
     * @return void
     */
    function reset_grades_mod($mod, $instance, $user) {
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

    /*
     * reset_grades_assignment
     *
     * @param object $instance
     * @param object $user
     * @return void
     */
    function reset_grades_assignment($instance, $user) {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/mod/assignment/lib.php');

        // remove assignment submissions and grades
        $select = 'assignment = ? AND userid =?';
        $params = array($instance->id, $user->id);
        $DB->delete_records_select('assignment_submissions', $select, $params);
        assignment_update_grades($instance, $user->id);
    }

    /*
     * reset_grades_quiz
     *
     * @param object $instance
     * @param object $user
     * @return void
     */
    function reset_grades_quiz($instance, $user) {
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
}
