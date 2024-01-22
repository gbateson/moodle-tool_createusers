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
defined('MOODLE_INTERNAL') || die();

/** Include required files */
require_once($CFG->dirroot.'/lib/formslib.php');

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

    protected $numeric   = null;
    protected $lowercase = null;
    protected $uppercase = null;

    // should we allow student/teacher enolments
    protected $allow_role_enrolments = false;
    protected $allow_student_enrolments = true;
    protected $allow_teacher_enrolments = true;

    protected $default_enrol_method = 'manual';
    protected $default_enrol_role = 'student';
    protected $default_enrol_status = ENROL_USER_ACTIVE;

    // we can restrict the list of student-enrollable courses
    // to a single course (usually the current course)
    protected $forcecourseid = 0;

    const SIZE_INT       = 4;
    const SIZE_SHORTTEXT = 4;
    const SIZE_TEXT      = 18;
    const SIZE_LONGTEXT  = 24;

    const SQL_LIKE       = 0;
    const SQL_REGEX      = 1;

    const TYPE_FIXED     = 1;
    const TYPE_RANDOM    = 2;
    const TYPE_SEQUENCE  = 3;
    const TYPE_USERID    = 4;
    const TYPE_USERNAME  = 5;

    /**
     * constructor
     */
    public function __construct($action=null, $customdata=null, $method='post', $target='', $attributes=null, $editable=true) {
        $this->numeric   = array_flip(str_split('23456789', 1));
        $this->lowercase = array_flip(str_split('abdeghjmnpqrstuvyz', 1));
        $this->uppercase = array_flip(str_split('ABDEGHJLMNPQRSTUVWXYZ', 1));
        if (method_exists('moodleform', '__construct')) {
            parent::__construct($action, $customdata, $method, $target, $attributes, $editable);
        } else {
            parent::moodleform($action, $customdata, $method, $target, $attributes, $editable);
        }
    }

    /**
     * definition
     */
    public function definition() {
        global $CFG, $DB, $USER;

        $mform = $this->_form;
        $this->set_form_id($mform, get_class($this));

        $tool = 'tool_createusers';
        $dot = get_string('stringseparator', $tool);

        $username_chars = '/^[a-z0-9._-]+$/';
        $username_error = get_string('error_usernamelowercase', $tool);


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

        $prefixes = $this->get_username_prefixes();

        // reuse usernames
        $options = array();
        if ($DB->sql_regex_supported()) {
            $options[self::SQL_REGEX] = get_string('sqlregex', $tool);
            $default = self::SQL_REGEX;
        } else {
            $default = self::SQL_LIKE;
        }
        $options[self::SQL_LIKE] = get_string('sqllike', $tool);

        foreach (array('include', 'exclude') as $type) {
            $name = 'oldusernames'.$type;
            $label = get_string($name, $tool);
            $elements = array();
            $elements[] = $mform->createElement('select', $name.'type', get_string($name.'type', $tool), $options);
            $elements[] = $mform->createElement('text', $name.'text', get_string($name.'text', $tool), array('size' => self::SIZE_TEXT));
            if ($prefixes) {
                $elements[] = $mform->createElement('select', $name.'prefix', '', $prefixes);
            }
            $mform->addElement('group', $name, $label, $elements, ' ', false);
            $mform->setType($name.'text', PARAM_TEXT);
            $mform->setType($name.'type', PARAM_INT);
            $mform->setDefault($name.'type', $default);
        }

        // username prefix
        $name = 'usernameprefix';
        $label = get_string('lowercaseprefix', $tool);
        $mform->addElement('text', $name, $label, array('size' => self::SIZE_TEXT));
        $mform->setType($name, PARAM_TEXT);
        $mform->addRule($name, $username_error, 'regex', $username_chars);
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
        $label = get_string('lowercasesuffix', $tool);
        $mform->addElement('text', $name, $label, array('size' => self::SIZE_TEXT));
        $mform->setType($name, PARAM_TEXT);
        $mform->addRule($name, $username_error, 'regex', $username_chars);
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
        $mform->addElement('text', $name, $label, array('size' => self::SIZE_SHORTTEXT));
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
        $mform->addElement('text', $name, $label, array('size' => self::SIZE_SHORTTEXT));
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
        // Student/Guest enrolments
        // ==================================
        //
        if ($this->allow_role_enrolments || $this->allow_student_enrolments) {

            if ($this->allow_role_enrolments) {
                $this->add_heading($mform, 'enrolments', 'enrol', true);
            } else {
                $this->add_heading($mform, 'studentenrolments', $tool, true);
            }

            $enrolmethods = array();
            $enrolids = array();
            $roleids = array();

            $default_enrol_id = 0;
            $default_role_id = $DB->get_field('role', 'id', array('shortname' => $this->default_enrol_role));

            $disable_enrol_methods = array();
            $disable_enrol_ids = array();
            $allowed_methods = array('manual', 'self');

            $plugins = enrol_get_plugins(true);
            if (empty($this->course)) {
                foreach ($plugins as $method => $plugin) {
                    if (in_array($method, $allowed_methods)) {
                        $enrolmethods[$method] = get_string('pluginname', 'enrol_'.$method);
                    } else {
                        $disable_enrol_methods[] = $method;
                    }
                }
                $roleids = get_default_enrol_roles(context_system::instance());
            } else {
                $instances = enrol_get_instances($this->course->id, true);
                foreach ($instances as $id => $instance) {
                    $method = $instance->enrol;
                    if (empty($plugins[$method])) {
                        continue; // skip missing/disabled plugins
                    }
                    $plugin = $plugins[$method];
                    $enrolids[$id] = $plugin->get_instance_name($instance);
                    if ($plugin->allow_manage($instance) == false) {
                        $disable_enrol_ids[] = $id;
                    }
                    if ($method == $this->default_enrol_method) {
                        $default_enrol_id = $id;
                    }
                }
                $roleids = get_assignable_roles($this->course->context);
            }

            if (count($enrolmethods)) {
                $name = 'enrolmethod';
                $label = get_string('enrolmentmethod', 'enrol');
                $mform->addElement('select', $name, $label, $enrolmethods);
                $mform->setType($name, PARAM_ALPHANUM);
                $mform->setDefault($name, $this->default_enrol_method);
            }

            if (count($enrolids)) {
                $name = 'enrolid';
                $label = get_string('enrolmentmethod', 'enrol');
                $mform->addElement('select', $name, $label, $enrolids);
                $mform->setType($name, PARAM_INT);
                $mform->setDefault($name, $default_enrol_id);
            }

            if (count($roleids)) {
                $name = 'roleid';
                $label = get_string('role');
                $mform->addElement('select', $name, $label, $roleids);
                $mform->setType($name, PARAM_INT);
                $mform->setDefault($name, $default_role_id);
            }

            // active or inactive (=suspended)
            if (count($enrolmethods) || count($enrolids)) {
                $name = 'enrolstatus';
                $label = get_string('status');
                $options = array(0 => get_string('active'),
                                 1 => get_string('suspended'));
                $mform->addElement('select', $name, $label, $options);
                $mform->setType($name, PARAM_INT);
                $mform->setDefault($name, $this->default_enrol_status);

                // disable status, if enrol method does not it to be suspended
                foreach ($disable_enrol_methods as $method) {
                    $mform->disabledIf($name, 'enrolmethod', 'eq', $method);
                }
                foreach ($disable_enrol_ids as $id) {
                    $mform->disabledIf($name, 'enrolid', 'eq', $id);
                }
            }

            // reset grades
            $name = 'resetgrades';
            $label = get_string($name, $tool);
            $mform->addElement('selectyesno', $name, $label);
            $mform->setType($name, PARAM_INT);
            $mform->setDefault($name, 1);

            // reset badges
            $name = 'resetbadges';
            $label = get_string($name, $tool);
            $mform->addElement('selectyesno', $name, $label);
            $mform->setType($name, PARAM_INT);
            $mform->setDefault($name, 1);

            // reset competencies
            $name = 'resetcompetencies';
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
            if (empty($this->forcecourseid)) {
                $select = 'id != ?';
                $params = array(SITEID);
            } else {
                $select = 'id = ?';
                $params = array($this->forcecourseid);
            }
            $courses = $DB->get_records_select_menu('course', $select, $params, 'shortname', 'id,shortname');
            $count = count($courses);
            if ($count <= 1) {
                if ($this->forcecourseid==0) {
                    $courses = array(0 => '') + $courses;
                }
                $params = array();
            } else {
                $params = array('multiple' => 'multiple', 'size' => min($count, 5));
            }
            foreach ($courses as $courseid => $shortname) {
                $courses[$courseid] = format_string($shortname);
            }
            $mform->addElement('select', $name, $label, $courses, $params);
            $mform->setType($name, PARAM_INT);
            $mform->setDefault($name, 0);

            // get groups menu
            if ($this->forcecourseid==0) {
                $groups = false;
            } else {
                $groups = groups_get_all_groups($this->forcecourseid, 0, 0, 'id,name');
            }

            // enrol in the following groups
            $name = 'enrolgroups';
            $label = get_string($name, $tool);
            if (empty($groups)) {
                $mform->addElement('text', $name, $label, array('size' => self::SIZE_LONGTEXT));
                $mform->setType($name, PARAM_TEXT);
                $mform->setDefault($name, '');
            } else {
                foreach ($groups as $groupid => $group) {
                    $groups[$groupid] = format_string($group->name);
                }
                $count = count($groups);
                if ($count <= 1) {
                    $params = array();
                } else {
                    $params = array('multiple' => 'multiple', 'size' => min($count, 5));
                }
                $mform->addElement('select', $name, $label, $groups, $params);
            }
        }

        // ==================================
        // teacher enrolments
        // ==================================
        //
        if ($this->allow_teacher_enrolments) {
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

            // get available course formats
            $formats = get_sorted_course_formats(true);
            $formats = array_flip($formats);
            foreach (array_keys($formats) as $format) {
                $formats[$format] = get_string('pluginname', "format_$format");
            }

            // course format
            $name = 'courseformat';
            $label = get_string($name, $tool);
            $mform->addElement('select', $name, $label, $formats);
            $mform->setType($name, PARAM_PLUGIN);
            $mform->setDefault($name, 'topics');
            $mform->addHelpButton($name, 'format', 'moodle');

            // number of sections (=weeks/topics/discussions)
            $name = 'numsections';
            $label = get_string($name, $tool);
            $mform->addElement('select', $name, $label, range(0, 10));
            $mform->setType($name, PARAM_INT);
            $mform->setDefault($name, 0);
            $mform->addHelpButton($name, $name, $tool);

            // enrol the following students in each teacher's course
            $name = 'enrolstudents';
            $label = get_string($name, $tool);
            $select = $DB->sql_like('username', '?').' AND deleted = ?';
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
        }

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
        if (class_exists('core_date')) {
            // Moodle >= 2.9
            $zones = core_date::get_list_of_timezones();
        } else {
            // Moodle <= 2.8
            $zones = get_list_of_timezones();
        }
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
        $label = get_string('preferredlanguage');
        $langs = get_string_manager()->get_list_of_translations();
        $mform->addElement('select', $name, $label, $langs);
        $mform->setType($name, PARAM_ALPHANUM);
        $mform->setDefault($name, $CFG->lang);

        // calendar type
        if (file_exists($CFG->dirroot.'/calendar/classes/type_factory.php')) {
            // Moodle >= 2.6
            $types = \core_calendar\type_factory::get_list_of_calendar_types();
        } else {
            // Moodle <= 2.5
            $types = array();
        }

        $name = 'calendartype';
        if (empty($CFG->$name)) {
            $default = '';
        } else {
            $default = $CFG->$name;
        }
        switch (count($types)) {
            case 0:
                $mform->addElement('hidden', $name, $default);
                $mform->setType($name, PARAM_ALPHA);
                break;
            case 1:
                $mform->addElement('hidden', $name, key($types));
                $mform->setType($name, PARAM_ALPHA);
                break;
            default:
                // Must be Moodle >= 2.6 to get here,
                // so we can use ""preferredcalendar" string.
                $label = get_string('preferredcalendar', 'calendar');
                $mform->addElement('select', $name, $label, $types);
                $mform->setDefault($name, $default);
                $mform->setType($name, PARAM_ALPHA);
        }

        // description
        $name = 'description';
        $label = get_string('userdescription');
        $mform->addElement('editor', $name, $label);
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
            $mform->addElement('html', $js);
        }

        // we do client side validation on these fields ourselves
        // but it should be possible using "addRule(..., 'client')"
        $js = '';
        $js .= '<script type="text/javascript">'."\n";
        $js .= "//<![CDATA[\n";

        $js .= "window.addEvent = function(elm, evt, fn){\n";
        $js .= "    if (elm.addEventListener) {\n";
        $js .= "        elm.addEventListener(evt, fn, false);\n";
        $js .= "    } else if (elm.attachEvent) {\n";
        $js .= "        elm.attachEvent('on' + evt, fn);\n";
        $js .= "    }\n";
        $js .= "};\n";

        $js .= "(function() {\n";
        $js .= "    var fn = function() {\n";
        $js .= "        var ids = ['prefix', 'suffix'];\n";
        $js .= "        var i_max = ids.length;\n";
        $js .= "        for (var i=0; i<i_max; i++) {\n";
        $js .= "            var id = 'id_username' + ids[i];\n";
        $js .= "            var obj = document.getElementById(id);\n";
        $js .= "            if (obj) {\n";
        $js .= "                window.addEvent(obj, 'change', function(){\n";
        $js .= "                    var r = new RegExp('[^a-zA-Z0-9._-]+', 'g');\n";
        $js .= "                    this.value = this.value.replace(r, '');\n";
        $js .= "                    this.value = this.value.toLowerCase();\n";
        $js .= "                });\n";
        $js .= "            }\n";
        $js .= "            obj = null;\n";
        $js .= "        }\n";
        $js .= "        var ids = ['includeprefix', 'excludeprefix'];\n";
        $js .= "        var i_max = ids.length;\n";
        $js .= "        for (var i=0; i<i_max; i++) {\n";
        $js .= "            var id = 'id_oldusernames' + ids[i];\n";
        $js .= "            var obj = document.getElementById(id);\n";
        $js .= "            if (obj) {\n";
        $js .= "                window.addEvent(obj, 'change', function(){\n";
        $js .= "                    var v = this.options[this.selectedIndex].value;\n";
        $js .= "                    var id = this.id.replace('prefix', '');\n";
        $js .= "                    var type = document.getElementById(id + 'type');\n";
        $js .= "                    switch (type.options[type.selectedIndex].value) {\n";
        $js .= "                        case '0': v = (v + '%'); break;\n"; // LIKE
        $js .= "                        case '1': v = ('^' + v); break;\n"; // REGEX
        $js .= "                    }\n";
        $js .= "                    document.getElementById(id + 'text').value = v;\n";
        $js .= "                });\n";
        $js .= "            }\n";
        $js .= "            obj = null;\n";
        $js .= "        }\n";
        $js .= "    };\n";
        $js .= "    window.addEvent(window, 'load', fn)\n";
        $js .= "}());\n";

        $js .= "//]]>\n";
        $js .= "</script>\n";
        $mform->addElement('html', $js);
    }

    /**
     * set_form_id
     *
     * @param  object $mform
     * @param  string $id
     * @return mixed default value of setting
     */
    protected function set_form_id($mform, $id) {
        $attributes = $mform->getAttributes();
        $attributes['id'] = $id;
        $mform->setAttributes($attributes);
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
     * add_field
     *
     * @param object $mform
     * @param string $plugin
     * @param string $type e.g. month, day, hour
     * @return void, but will modify $mform
     */
    protected function add_field($mform, $plugin, $name, $elementtype, $paramtype, $options=null, $default=null) {
        $configname = 'config_'.$name;
        $label = get_string($name, $plugin);
        $mform->addElement($elementtype, $configname, $label, $options);
        $mform->setType($configname, $paramtype);
        $mform->setDefault($configname, $this->get_original_value($name, $default));
        $mform->addHelpButton($configname, $name, $plugin);
    }

    /**
     * get_username_prefixes
     */
    public function get_username_prefixes($limit=20) {
        global $DB;

        $i_min = 2;
        $i_max = 20;
        $sql = array();
        $params = array();

        $regex = $DB->sql_regex_supported();
        if ($regex) {
            $where = 'username '.$DB->sql_regex().' ?';
        } else {
            // require "." or "_" or "-"
            $where = $DB->sql_like('username', '?').
              ' OR '.$DB->sql_like('username', '?').
              ' OR '.$DB->sql_like('username', '?');
            // exclude deleted users
            $where = $DB->sql_like('username', '?', false, false, true).' AND ('.$where.')';
        }
        $where = "deleted = ? AND $where";

        // Note that we cannot use "countrecords" in the HAVING clause, because
        // in PostgreSQL, you cannot use the column aliases in the HAVING clause.
        // https://www.postgresqltutorial.com/postgresql-tutorial/postgresql-having/

        for ($i=$i_min; $i<=$i_max; $i++) {
            $sql[] = 'SELECT '.$DB->sql_substr('username', 1, $i).' AS prefix, COUNT(*) AS countrecords '.
                     'FROM {user} '.
                     'WHERE '.$where.' '.
                     'GROUP BY prefix '.
                     'HAVING COUNT(*) >= 2';
            if ($regex) {
                array_push($params, 0, '^[^._-].*[._-]');
            } else {
                array_push($params, 0, '.%', '%.%', '%_%', '%-%');
            }
        }

        $sql = 'SELECT p.prefix, p.countrecords, LENGTH(p.prefix) AS prefixlength '.
               'FROM ('.implode(' UNION ', $sql).') p '.
               'ORDER BY p.countrecords DESC, prefixlength DESC, p.prefix ASC';

        $count = 0;
        if ($prefixes = $DB->get_records_sql_menu($sql, $params)) {
            foreach ($prefixes as $prefix => $countrecords) {
                if ($count >= $limit) {
                    unset($prefixes[$prefix]);
                } else {
                    $i_max = strlen($prefix) - 1;
                    for ($i=$i_min; $i<=$i_max; $i++) {
                        $p = substr($prefix, 0, $i);
                        if (array_key_exists($p, $prefixes) && $prefixes[$p]==$countrecords) {
                            unset($prefixes[$p]);
                        }
                    }
                    if (array_key_exists($prefix, $prefixes)) {
                        $prefixes[$prefix] = "$prefix ($countrecords)";
                        $count++;
                    }
                }
            }
        }

        if (empty($prefixes)) {
            $prefixes = array();
        } else {
            $prefixes = array('' => '') + $prefixes;
        }

        return $prefixes;
    }

    /**
     * create_users
     */
    public function create_users() {
        global $CFG, $DB, $USER;

        // get form data
        $data = $this->get_data();
        $time = time();

        $OLD = '';
        $NEW = get_string('new');
        $USED = '--';

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

        // disallow REGEX if DB does not support them
        if (! $DB->sql_regex_supported()) {
            if (! empty($data->oldusernamesincludetext)) {
                if ($data->oldusernamesincludetype==self::SQL_REGEX) {
                    $data->oldusernamesincludetext = '';
                }
            }
            if (! empty($data->oldusernamesexcludetext)) {
                if ($data->oldusernamesexcludetype==self::SQL_REGEX) {
                    $data->oldusernamesexcludetext = '';
                }
            }
        }

        $userids = array();
        if (! empty($data->oldusernamesincludetext)) {

            // do not alter admin users or current user
            $users = get_admins();
            foreach ($users as $user) {
                $users[$user->id] = $user->username;
            }
            $users[$USER->id] = $USER->username;

            list($select, $params) = $DB->get_in_or_equal($users);
            $select = "NOT (username $select)";

            // add included users, but ignore excluded users
            if ($data->oldusernamesincludetype==self::SQL_REGEX) {
                $select .= ' AND username '.$DB->sql_regex(true).' ? ';
                $params[] = $data->oldusernamesincludetext;
                if (! empty($data->oldusernamesexcludetext)) {
                    $select .= ' AND username '.$DB->sql_regex(false).' ?';
                    $params[] = $data->oldusernamesexcludetext;
                }
            } else {
                $select .= ' AND '.$DB->sql_like('username', '?');
                $params[] = $data->oldusernamesincludetext;
                if (! empty($data->oldusernamesexcludetext)) {
                    $select .= ' AND '.$DB->sql_like('username', '?', false, false, true);
                    $params[] = $data->oldusernamesexcludetext;
                }
            }

            if ($users = $DB->get_records_select('user', $select, $params, 'id', 'id, username')) {
                $userids = array_keys($users);
            }
            unset($users, $user, $select, $params);
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

        // Detect Booststrap themes
        // theme/boost: Moodle >= 3.2
        // theme/bootstrapbase: Moodle 2.5 - 3.6
        $bootstrap = (file_exists($CFG->dirroot.'/theme/boost') ||
                      file_exists($CFG->dirroot.'/theme/bootstrapbase'));

        // set HTML attributes for TABLE and THEAD 
        if ($bootstrap) {
            $table_params = array('class' => 'table table-striped', 'border' => 1,
                                  'style' => 'max-width: 100%; width: max-content;');
            $thead_params = array('class' => 'thead-dark'); // similar to "bg-dark text-light"
            $cell_params = null;
        } else {
            $table_params = array('class' => 'createusers', 'border' => 1,
                                  'cellspacing' => 4,'cellpadding' => 4,
                                  'style' => 'max-width: 100%; width: max-content;');
            $thead_params = array('class' => 'headings', 'bgcolor' => '#eebbee');
            $cell_params = array('class' => '',
                                 'style' => 'border: 1px solid black; '.
                                            'border-collapse: collapse; '.
                                            'padding: 4px; '.
                                            'text-align: center;');
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
            } else if (count($userids)) {
                $user->id = array_shift($userids);
                $user->newuser = $DB->get_field('user', 'username', array('id' => $user->id));
                $DB->update_record('user', $user);
            } else {
                unset($user->id);
                $user->id = $DB->insert_record('user', $user);
                $user->newuser = $NEW;
            }

            // fix enrolments and grades
            $category = $this->fix_enrolments($data, $user, $time);

            // print headings (first time only)
            if ($table=='') {
                $table .= html_writer::start_tag('table', $table_params);
                $table .= html_writer::start_tag('thead', $thead_params);
                $table .= html_writer::start_tag('tr');
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
                    $cell_params['class'] = $column;
                    $table .= html_writer::tag('th', $heading, $cell_params);
                }
                $table .= html_writer::end_tag('tr');
                $table .= html_writer::end_tag('thead');
                $table .= html_writer::start_tag('tbody');

                list($courses, $groups) = $this->format_courses_and_groups($data);
            }

            // print user data
            if ($bootstrap) {
                $params = null;
            } else {
                if ($i % 2) {
                    $params = array('class' => 'user odd', 'bgcolor' => '#eeeeaa');
                } else {
                    $params = array('class' => 'user even', 'bgcolor' => '#ffffee');
                }
            }
            $table .= html_writer::start_tag('tr', $params);
            foreach ($columns as $column) {
                $cell_params['class'] = $column;
                if ($column=='courses') {
                    $table .= html_writer::tag('td', $courses, $cell_params);
                } else if ($column=='groups') {
                    $table .= html_writer::tag('td', $groups, $cell_params);
                } else if ($column=='category') {
                    $table .= html_writer::tag('td', $category, $cell_params);
                } else {
                    $table .= html_writer::tag('td', $user->$column, $cell_params);
                }
            }
            $table .= html_writer::end_tag('tr');
        }

        if ($table) {
            $table .= html_writer::end_tag('tbody');
            $table .= html_writer::end_tag('table');
            echo $table;
        }

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
        $username = $this->create_name($data, 'username',  $num);
        $username = self::textlib('strtolower', $username);
        $rawpassword = $this->create_name($data, 'password',  $num, $username);
        $firstname = $this->create_name($data, 'firstname', $num, $username);
        $lastname = $this->create_name($data, 'lastname',  $num, $username);
        $alternatename = $this->create_name($data, 'alternatename', $num, $username);

        // Hash the password in normal Moodle way.
        // In Moodle >= 2.5, there is a $fasthash parameter,
        // but we don't use it to maintain backwards compatability.
        if (function_exists('hash_internal_user_password')) {
            // Moodle >= 1.6
            $password = hash_internal_user_password($rawpassword);
        } else {
            // Moodle <= 1.5
            $password = md5($rawpassword);
        }

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
        $calendartype = $data->calendartype;
        $description = $data->description;
        $mnethostid = $CFG->mnet_localhost_id;

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
            'rawpassword' => $rawpassword,
            'idnumber'  => '',
            'firstname' => $firstname,
            'lastname'  => $lastname,
            'email'     => $username.'@localhost.invalid',
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
            'calendartype'  => $calendartype
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
        if ($data->resetbadges) {
            $this->reset_badges($user);
        }
        if ($data->resetcompetencies) {
            $this->reset_competencies($user);
        }
        if ($data->cancelenrolments) {
            enrol_user_delete($user); // lib/enrollib.php
        }
        if ($data->cancelroles) {
            role_unassign_all(array('userid' => $user->id)); // lib/accesslib.php
            $DB->delete_records('groups_members', array('userid' => $user->id));
        }

        if ($this->allow_role_enrolments==false && $this->allow_student_enrolments==false) {
            $data->enrolgroups = null;
        }
        if ($this->allow_teacher_enrolments==false) {
            $data->enrolcategory = null;
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
            if (is_array($data->enrolgroups)) {
                // array of groupids
                $groups = $data->enrolgroups;
            } else {
                // comma-delimited string of group names
                $groups = explode(',', $data->enrolgroups);
            }
            $groups = array_map('trim', $groups);
            $groups = array_filter($groups);
        }

        $courseformats = get_sorted_course_formats(true);
        $courseformats = array_flip($courseformats);

        $enrolmethod = (empty($data->enrolmethod) ? '' : $data->enrolmethod);
        $enrolid     = (empty($data->enrolid)     ? 0  : $data->enrolid);
        $roleid      = (empty($data->roleid)      ? 0  : $data->roleid);
        $enrolstatus = (empty($data->enrolstatus) ? 0  : $data->enrolstatus);

        foreach ($courseids as $courseid) {
            if ($context = self::context(CONTEXT_COURSE, $courseid)) {

                if ($role = $this->get_role_from_id($roleid)) {
                    $this->get_role_assignment($context->id, $role->id, $user->id, $time);
                    if (method_exists($context, 'mark_dirty')) {
                        // Moodle >= 2.2
                        $context->mark_dirty();
                    } else {
                        // Moodle <= 2.1
                        mark_context_dirty($context->path);
                    }
                    if ($enrolid) {
                        $enrol = $this->get_enrol_from_id($courseid, $enrolid);
                    } else {
                        $enrol = $this->get_enrol_from_method($courseid, $role->id, $user->id, $time, $enrolmethod);
                    }
                    if ($enrol) {
                        $enrolment = $this->get_user_enrolment($enrol->id, $user->id, $time, $enrolstatus);
                        foreach ($groups as $group) {
                            if (is_numeric($group)) {
                                $groupid = $DB->get_field('groups', 'id', array('id' => $group, 'courseid' => $courseid));
                            } else {
                                $groupid = $this->get_groupid($courseid, $group, $time);
                            }
                            if ($groupid) {
                                $this->get_group_memberid($groupid, $user->id, $time);
                            }
                        }
                    }
                }
            }
            if (function_exists('groups_cache_groupdata')) {
                groups_cache_groupdata($courseid); // Moodle >= 3.0
            }
        }

        $category = '';
        $tool = 'tool_createusers';
        if ($data->enrolcategory) {

            // set course shortname
            if ($data->doublebyte) {
                $shortname = mb_convert_kana($user->username, 'AS', 'UTF-8');
            } else {
                $shortname = $user->username;
            }
            $fullname = $this->get_multilang_string('courseforuser', $tool, $shortname);

            // should we reset the format and numsections for this this course?
            if ($DB->record_exists('course', array('shortname' => $shortname))) {
                $set_format_and_numsections = false;
            } else {
                $set_format_and_numsections = true;
            }

            if ($courseid = $this->get_user_courseid($data->enrolcategory, $shortname, $fullname, $time)) {
                if ($context = self::context(CONTEXT_COURSE, $courseid)) {

                    // enrol new $user as an "editingteacher"
                    if ($role = $this->get_role_from_name('editingteacher')) {
                        $this->get_role_assignment($context->id, $role->id, $user->id, $time);
                        if (method_exists($context, 'mark_dirty')) {
                            // Moodle >= 2.2
                            $context->mark_dirty();
                        } else {
                            // Moodle <= 2.1
                            mark_context_dirty($context->path);
                        }
                        if ($enrol = $this->get_enrol_from_method($courseid, $role->id, $user->id, $time, 'manual')) {
                            $this->get_user_enrolment($enrol->id, $user->id, $time);
                        }
                    }

                    // enrol "student" users
                    if ($role = $this->get_role_from_name('student')) {
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
                            if ($enrol = $this->get_enrol_from_method($courseid, $role->id, $userid, $time, 'manual')) {
                                $this->get_user_enrolment($enrol->id, $userid, $time);
                            }
                        }
                    }

                    // add course files respository
                    if ($path = preg_replace('/[\/\\\\](\.*[\/\\\\])+/', '/', $data->folderpath)) {
                        $this->get_repository_instance_id($context, $user->id, "$user->username files", $path, 1, true);
                    }
                }

                // remove everything from course
                if ($data->resetcourses) {

                    // remove all labels, resources and activities
                    if ($cms = $DB->get_records('course_modules', array('course' => $courseid), '', 'id,course')) {
                        foreach ($cms as $cm) {
                            $this->remove_coursemodule($cm->id);
                        }
                    }

                    // remove all blocks
                    $context = self::context(CONTEXT_COURSE, $courseid);
                    blocks_delete_all_for_context($context->id);

                    // remove all badges
                    if ($badges = $DB->get_records('badge', array('courseid' => $courseid), '', 'id,courseid')) {
                        foreach ($badges as $badge) {
                            $badge = new badge($badge->id);
                            $badge->delete(false);
                        }
                    }

                    // force reset of course format and numsections
                    $set_format_and_numsections = true;
                }

                // set course fromat and numsections, if required
                if ($set_format_and_numsections) {
                    if (isset($data->courseformat) && array_key_exists($data->courseformat, $courseformats)) {
                        $DB->set_field('course', 'format', $data->courseformat, array('id' => $courseid));
                    }
                    if (isset($data->numsections) && is_numeric($data->numsections)) {
                        if (function_exists('course_get_format')) {
                            // Moodle >= 2.3
                            $options = course_get_format($courseid)->get_format_options();
                            $options['numsections'] = $data->numsections;
                            course_get_format($courseid)->update_course_format_options($options);
                        } else {
                            // Moodle <= 2.2
                            $DB->set_field('course', 'numsections', $data->numsections, array('id' => $courseid));
                        }
                    }
                }

                // format link to course
                $url = new moodle_url('/course/view.php', array('id' => $courseid));
                $category = html_writer::link($url, $shortname, array('target' => '_blank'));
            }
        }
        return $category;
    }

    /**
     * context
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
    public static function context($contextlevel, $instanceid=0, $strictness=0) {
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
        if (class_exists('\\core_user\\fields')) { // Moodle >= 3.11
            $fields = \core_user\fields::for_userpic();
            if ($extrafields) {
                $fields->including($extrafields);
            }
            $fields = $fields->get_sql($tableprefix, false, $fieldprefix, $idalias, false)->selects;
            if ($tableprefix === '') {
                $fields = str_replace('{user}.', '', $fields);
            }
            return str_replace(', ', ',', $fields);
            // id, picture, firstname, lastname, firstnamephonetic, lastnamephonetic, middlename, alternatename, imagealt, email
        }
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
     * get_role_from_id
     *
     * @param string $name
     * @return object or boolean (FALSE)
     */
    public function get_role_from_id($id) {
        global $DB;
        return $DB->get_record('role', array('id' => $id));
    }

    /**
     * get_role_from_name
     *
     * @param string $name
     * @return object or boolean (FALSE)
     */
    public function get_role_from_name($name) {
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
     * get_enrol_from_id
     *
     * @param integer $courseid
     * @param integer $enrolid from "enrol" table
     * @return object or boolean (FALSE)
     */
    public function get_enrol_from_id($courseid, $enrolid) {
        global $DB;
        $params = array('id' => $enrolid,
                        'courseid' => $courseid);
        return $DB->get_record('enrol', array('id' => $enrolid));
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
    public function get_enrol_from_method($courseid, $roleid, $userid, $time, $enrolmethod) {
        global $DB;

        if (empty($enrolmethod)) {
            return false; // shouldn't happen !!
        }

        $params = array('enrol' => $enrolmethod,
                        'courseid' => $courseid,
                        'roleid' => $roleid);
        if ($record = $DB->get_record('enrol', $params)) {
            if ($record->status == ENROL_INSTANCE_DISABLED) {
                $record->status = ENROL_INSTANCE_ENABLED;
                if ($DB->update_record('enrol', $record)) {
                    if (class_exists('\\core\\event\\enrol_instance_updated')) { // Moodle >= 3.0
                        \core\event\enrol_instance_updated::create_from_record($record)->trigger();
                    }
                }
            }
            return $record;
        }

        $record = (object)array(
            'enrol'        => $enrolmethod,
            'status'       => ENROL_INSTANCE_ENABLED,
            'courseid'     => $courseid,
            'roleid'       => $roleid,
            'modifierid'   => $userid,
            'timecreated'  => $time,
            'timemodified' => $time
        );
        if ($record->id = $DB->insert_record('enrol', $record)) {
            if (class_exists('\\core\\event\\enrol_instance_created')) { // Moodle >= 3.0
                \core\event\enrol_instance_created::create_from_record($record)->trigger();
            }
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
        $params = array('roleid' => $roleid,
                        'contextid' => $contextid,
                        'userid' => $userid);
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
    public function get_user_enrolment($enrolid, $userid, $time, $status=ENROL_USER_ACTIVE) {
        global $DB, $USER;
        $params = array('enrolid' => $enrolid,
                        'userid' => $userid);
        if ($record = $DB->get_record('user_enrolments', $params)) {
            $record->status = $status;
            $record->timestart = $time;
            $record->timeend = 0;
            if ($DB->update_record('user_enrolments', $record)) {
                return $record;
            }
        } else {
            $record = (object)array(
                'status'       => $status,
                'enrolid'      => $enrolid,
                'userid'       => $userid,
                'modifierid'   => $USER->id,
                'timestart'    => $time,
                'timeend'      => 0,
                'timecreated'  => $time,
                'timemodified' => $time
            );
            if ($record->id = $DB->insert_record('user_enrolments', $record)) {
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
        $instanceids = array();

        // get $user's grades
        if ($grades = $DB->get_records_menu('grade_grades', array('userid' => $user->id), null, 'id,itemid')) {

            // remove all $user's grades (from any course)
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

                if (empty($instanceids[$mod])) {
                    $instanceids[$mod] = array();
                }
                $instanceids[$mod][$instance->id] = true;
            }
        }

        // reset other mods with no grade item
        $this->reset_grades_mods($user, $instanceids);
    }

    /**
     * get_modnames
     *
     * @return array of mod names
     */
    public function get_modnames() {
        global $DB;
        static $modnames = null;
        if ($modnames===null) {
            $modnames = $DB->get_records_menu('modules', array(), 'name', 'id,name');
        }
        return $modnames;
    }

    /**
     * get_mod_tablenames_with_userid
     *
     * @param string $modname
     * @return array of mod names
     */
    public function get_mod_tablenames_with_userid($modname='') {
        global $DB;

        static $tables = null;
        if ($tables===null) {
            $tables = $this->get_modnames();
            $tables = '/^(?:'.implode('|', $tables).')_/';
            $tables = preg_grep($tables, $DB->get_tables());
            foreach ($tables as $t => $table) {
                $columns = $DB->get_columns($table);
                if (! array_key_exists('userid', $columns)) {
                    unset($tables[$t]);
                }
            }
        }

        if ($modname=='') {
            return $tables;
        } else {
            return preg_grep('/^'.$modname.'_/', $tables);
        }
    }

    /**
     * reset_grades_mods
     *
     * @param object $user
     * @param array  $instanceids that have already had grades reset
     * @return void
     */
    public function reset_grades_mods($user, $instanceids) {
        global $DB;
        $mods = $this->get_modnames();
        foreach ($mods as $mod) {
            if ($instances = $DB->get_records($mod)) {
                foreach ($instances as $instance) {
                    if (empty($instanceids[$mod][$instance->id])) {
                        $this->reset_grades_mod($mod, $instance, $user);
                        $instanceids[$mod][$instance->id] = true;
                    }
                }
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

        $tables = $this->get_mod_tablenames_with_userid($mod);
        foreach ($tables as $table) {
            $DB->delete_records($table, array('userid' => $user->id));
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
     * reset_badges
     *
     * @param object $user
     * @return void
     */
    public function reset_badges($user) {
        global $DB;

        // remove all badges issued automatically to this $user
        if ($badges = $DB->get_records('badge_issued', array('userid' => $user->id), 'id', 'id,userid')) {
            list($select, $params) = $DB->get_in_or_equal(array_keys($badges));
            $DB->delete_records_select('badge_issued', "id $select", $params);
            $DB->delete_records_select('badge_criteria_met', "issuedid $select", $params);
        }

        // remove all external and manual badges awarded to, or by, this $user
        $params = array($user->id);
        $DB->delete_records_select('badge_backpack', 'userid = ?', $params);
        $DB->delete_records_select('badge_manual_award', 'issuerid = ?', $params);
        $DB->delete_records_select('badge_manual_award', 'recipientid = ?', $params);
    }

    /**
     * reset_competencies
     *
     * @param object $user
     * @return void
     */
    public function reset_competencies($user) {
        global $DB;

        if (get_config('core_competency', 'enabled')) { // Moodle >= 3.1

            $params = array('userid' => $user->id);
            if ($ids = $DB->get_records_menu('competency_usercomp', $params, 'id,competencyid')) {
                $ids = array_keys($ids);
                list($select, $params) = $DB->get_in_or_equal($ids);
                $DB->delete_records_select('competency_usercomp', "id $select", $params);
                $DB->delete_records_select('competency_evidence', "usercompetencyid $select", $params);
            }

            $select = 'userid = ?';
            $params = array($user->id);
            $DB->delete_records_select('competency_usercompcourse', $select, $params);
            $DB->delete_records_select('competency_usercompplan', $select, $params);
            $DB->delete_records_select('competency_plan', $select, $params);

            $params = array('userid' => $user->id);
            if ($ids = $DB->get_records_menu('competency_userevidence', $params, 'id,userid')) {
                $ids = array_keys($ids);
                list($select, $params) = $DB->get_in_or_equal($ids);
                $DB->delete_records_select('competency_userevidence', "id $select", $params);
                $DB->delete_records_select('competency_userevidencecomp', "userevidenceid $select", $params);
            }
        }
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
                $name = format_string($name);
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

        if (is_array($groups)) {
            // array of groupids
            $groupfield = 'id';
        } else {
            // comma-delimited string of group names
            $groups = explode(',', $groups);
            $groups = array_map('trim', $groups);
            $groups = array_filter($groups);
            $groupfield = 'name';
        }

        if (count($groups)) {
            list($groupselect, $groupparams) = $DB->get_in_or_equal($groups);
            $select = "courseid $courseselect AND $groupfield $groupselect";
            $params = array_merge($courseparams, $groupparams);
            if ($groups = $DB->get_records_select('groups', $select, $params, 'name', 'id,courseid,name')) {
                foreach ($groups as $id => $group) {
                    $name = format_string($group->name);
                    $params = array('id' => $group->courseid, 'group' => $id);
                    $url = new moodle_url('/group/index.php', $params);
                    $groups[$id] = html_writer::link($url, $name, array('target' => '_blank'));
                }
                $groups = implode(', ', $groups);
            } else {
                $groups = ''; // shouldn't happen !!
            }
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

        if (empty($data->enrolgroups)) {
            $groups = array();
        } else {
            $groups = $data->enrolgroups;
            if (is_array($groups)) {
                if (count($groups)) {
                    list($select, $params) = $DB->get_in_or_equal($groups);
                    $groups = $DB->get_records_select_menu('groups', "id $select", $params, 'name', 'id,name');
                }
            } else {
                $groups = explode(',', $groups);
                $groups = array_map('trim', $groups);
                $groups = array_filter($groups);
            }
        }

        $courses = $data->enrolcourses;
        if (! is_array($courses)) {
            $courses = explode(',', $courses);
            $courses = array_filter($courses);
        }

        list($select, $params) = $DB->get_in_or_equal($courses);

        $links = '';
        if ($courses = $DB->get_records_select('course', "id $select", $params, 'id', 'id,shortname')) {
            foreach ($courses as $course) {
                if (empty($groups)) {
                    if ($cm = $this->add_login_resource($course->id, $table)) {
                        $url = new moodle_url('/mod/page/view.php', array('id' => $cm->id));
                        $link = html_writer::link($url, format_string($cm->name), array('target' => '_blank'));
                        $links .= html_writer::tag('li', $link);
                    }
                } else {
                    foreach ($groups as $group) {
                        if ($cm = $this->add_login_resource($course->id, $table, $group)) {
                            $url = new moodle_url('/mod/page/view.php', array('id' => $cm->id));
                            $link = html_writer::link($url, format_string($cm->name), array('target' => '_blank'));
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
     * @param  string  $groupname (optional, default='')
     * @param  integer $sectionnum (optional, default=0)
     * @return object  $cm course_module record of newly added/updated page resource
     */
    public function add_login_resource($courseid, $table, $groupname='', $sectionnum=0) {
        global $DB, $USER;

        static $pagemoduleid = null;
        if ($pagemoduleid===null) {
            $pagemoduleid = $DB->get_field('modules', 'id', array('name' => 'page'));
        }

        if ($groupname=='') {
            $name = get_string('userlogindetails', 'tool_createusers');
        } else {
            $name = get_string('userlogindetailsgroup', 'tool_createusers', $groupname);
        }

        $select = 'cm.*, ? AS modname, ? AS modulename, p.name AS name';
        $from   = '{course_modules} cm '.
                  'JOIN {page} p ON cm.module = ? AND cm.instance = p.id';
        $where  = 'p.course = ? AND p.name = ?';
        $params = array('page', 'page', $pagemoduleid, $courseid, $name);
        $order  = 'cm.visible DESC, cm.added DESC'; // newest, visible cm first

        if ($cm = $DB->get_records_sql("SELECT $select FROM $from WHERE $where ORDER BY $order", $params, 0, 1)) {
            $cm  = reset($cm);
            $cm->content = $table;
            $DB->set_field('page', 'content', $table, array('id' => $cm->instance));

            // Trigger mod_updated event with information about this page resource.
            if (class_exists('\\core\\event\\course_module_updated')) {
                // Moodle >= 2.6
                \core\event\course_module_updated::create_from_cm($cm)->trigger();
            } else {
                $event = (object)array(
                    'cmid'       => $cm->id,
                    'courseid'   => $cm->course,
                    'modulename' => $cm->modulename,
                    'name'       => $cm->name,
                    'userid'     => $USER->id
                );
                if (function_exists('events_trigger_legacy')) {
                    // Moodle 2.6 - 3.0 ... so not used here anymore
                    events_trigger_legacy('mod_updated', $event);
                } else {
                    // Moodle <= 2.5
                    events_trigger('mod_updated', $event);
                }
            }
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
                'modname'         => 'page',
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

            // Trigger mod_created event with information about this page resource.
            if (class_exists('\\core\\event\\course_module_created')) {
                // Moodle >= 2.6
                \core\event\course_module_created::create_from_cm($cm)->trigger();
            } else {
                $event = (object)array(
                    'cmid'       => $cm->id,
                    'courseid'   => $cm->course,
                    'modulename' => $cm->modulename,
                    'name'       => $cm->name,
                    'userid'     => $USER->id
                );
                if (function_exists('events_trigger_legacy')) {
                    // Moodle 2.6 - 3.0 ... so not used here anymore
                    events_trigger_legacy('mod_created', $event);
                } else {
                    // Moodle <= 2.5
                    events_trigger('mod_created', $event);
                }
            }
        }

        // rebuild_course_cache (needed for Moodle 2.0)
        rebuild_course_cache($courseid, true);

        return $cm;
    }

    /**
     * get_user_courseid
     *
     * @param integer $categoryid
     * @param string  $shortname
     * @param integer $time
     * @return mixed return id if a course was located/created, FALSE otherwise
     */
    public function get_user_courseid($categoryid, $shortname, $fullname, $time, $numsections=3, $format='topics') {
        global $CFG, $DB;

        if ($course = $DB->get_record('course', array('shortname' => $shortname))) {
            $DB->set_field('course', 'category', $categoryid, array('id' => $course->id));
            return $course->id;
        }

        // create new course
        $course = (object)array(
            'category'      => $categoryid, // crucial !!
            'fullname'      => $fullname,
            'shortname'     => $shortname,
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

        if (empty($course)) {
            return false; // shouldn't happen !!
        }

        if ($sortorder = $DB->get_field('course', 'MAX(sortorder)', array())) {
            $sortorder ++;
        } else {
            $sortorder = 100;
        }
        $DB->set_field('course', 'sortorder', $sortorder, array('id' => $course->id));

        return $course->id;
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
     * @param boolean  $deleteothers if TRUE delete other filesystem instances in this context
     * @return integer id from repository_instances table
     */
    public function get_repository_instance_id($context, $userid, $name, $path, $relativefiles, $deleteothers=false) {
        $instanceid = 0;
        $type = 'filesystem';
        $params = array('type' => $type, 'currentcontext' => $context, 'context' => array($context), 'userid' => $userid);
        if ($instances = repository::get_instances($params)) {
            foreach ($instances as $instance) {
                if ($instance->get_option('fs_path')==$path) {
                    $params = array('name' => $name, 'fs_path' => $path, 'relativefiles' => $relativefiles);
                    $instance->set_option($params);
                    $instanceid = $instance->id;
                } else if ($deleteothers) {
                    $instance->delete();
                }
            }
        }
        if ($instanceid==0) {
            $params = array('name' => $name, 'fs_path' => $path, 'relativefiles' => $relativefiles);
            $instanceid = repository::static_function($type, 'create', $type, $userid, $context, $params);
        }
        return $instanceid;
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

    /**
     * usort_langs
     *
     * sort $langs, so that "en" is first
     * and parent langs (length = 2)
     * appear before child langs (length > 2)
     */
    static public function usort_langs($a, $b) {
        if ($a=='en') {
            return -1;
        }
        if ($b=='en') {
            return 1;
        }
        // compare parent langs
        $a_parent = substr($a, 0, 2);
        $b_parent = substr($b, 0, 2);
        if ($a_parent < $b_parent) {
            return -1;
        }
        if ($b_parent < $a_parent) {
            return 1;
        }
        // same parent lang, compare lengths
        $a_len = strlen($a);
        $b_len = strlen($b);
        if ($a_len < $b_len) {
            return -1;
        }
        if ($b_len < $a_len) {
            return 1;
        }
        // sibling langs, compare values
        if ($a < $b) {
            return -1;
        }
        if ($b < $a) {
            return 1;
        }
        return 0; // shouldn't happen !!
    }

    /**
     * get_string
     *
     * @param object $strman
     * @return array sorted list of language codes used on this site
     */
    static public function get_langs($strman) {
        static $langs = null;
        if ($langs===null) {
            $langs = $strman->get_list_of_translations();
            $langs = array_keys($langs);
            usort($langs, array('tool_createusers_form', 'usort_langs'));
            // sort $langs, so that "en" is first
            // and parent langs appear before child langs
        }
        return $langs;
    }

    /**
     * get_string
     *
     * @param string $identifier
     * @param string $component
     * @param mixed  $params
     * @return string, return the "multilang" verison of the required string;
     *                 i.e. <span lang="xx" class="multilang">...></span><span...>...</span>
     */
    static public function get_multilang_string($identifier, $component='', $params=null) {
        $strman = get_string_manager();
        $langs = self::get_langs($strman);
        $texts = array();
        foreach ($langs as $lang) {
            $strings = $strman->load_component_strings($component, $lang);
            if (array_key_exists($identifier, $strings)) {
                $text = $strman->get_string($identifier, $component, $params, $lang);
                if (array_search($text, $texts)===false) {
                    $texts[$lang] = $text;
                }
            }
        }

        // this string does not exist - should not happen !!
        if (empty($texts)) {
            return '';
        }

        // special case - this string occurs in only one language pack
        if (count($texts)==1) {
            return reset($texts);
        }

        // format strings as multilang $texts
        foreach ($texts as $lang => $text) {
            $params = array('lang' => $lang, 'class' => 'multilang');
            $texts[$lang] = html_writer::tag('span', $text, $params);
        }

        return implode('', $texts);
    }

    /**
     * textlib
     *
     * a wrapper method to offer consistent API for textlib class
     * in Moodle 2.0 and 2.1, $textlib is first initiated, then called
     * in Moodle 2.2 - 2.5, we use only static methods of the "textlib" class
     * in Moodle >= 2.6, we use only static methods of the "core_text" class
     *
     * @param string $method
     * @param mixed any extra params that are required by the textlib $method
     * @return result from the textlib $method
     * @todo Finish documenting this function
     */
    static public function textlib() {
        if (class_exists('core_text')) {
            // Moodle >= 2.6
            $textlib = 'core_text';
        } else if (method_exists('textlib', 'textlib')) {
            // Moodle 2.0 - 2.1
            $textlib = textlib_get_instance();
        } else {
            // Moodle 2.2 - 2.5
            $textlib = 'textlib';
        }
        $args = func_get_args();
        $method = array_shift($args);
        $callback = array($textlib, $method);
        return call_user_func_array($callback, $args);
    }
}
