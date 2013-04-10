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
 * Defines the editing form for the canvas question type.
 *
 * @package   qtype_canvas
 * @copyright 2013 Jacob Shapiro (ETHZ LET), based on 2012 Martin Vögeli (Voma) {@link http://moodle.ch/}, based on 2007 Jamie Pratt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Short canvas question editing form definition.
 *
 * @copyright  2013 Jacob Shapiro (ETHZ LET), based on 2012 Martin Vögeli (Voma), based on 2007 Jamie Pratt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class qtype_canvas_edit_form extends question_edit_form {

    
    const MAX_GROUPS = 8;
    const START_NUM_ITEMS = 6;
    const ADD_NUM_ITEMS = 3;
    
    /**
     *
     * Options shared by all file pickers in the form.
     */
    public static function file_picker_options() {
        $filepickeroptions = array();
        $filepickeroptions['accepted_types'] = array('web_image');
        $filepickeroptions['maxbytes'] = 0;
        $filepickeroptions['maxfiles'] = 1;
        $filepickeroptions['subdirs'] = 0;
        return $filepickeroptions;
    }
    
    /**
     * definition_inner adds all specific fields to the form.
     * @param object $mform (the form being built).
     */
    protected function definition_inner($mform) {
        
        /* Voma add start */
        global $PAGE;
        //Y: $PAGE->requires->js_init_call('M.qtype_canvas.init');
        // http://docs.moodle.org/dev/Using_jQuery_with_Moodle_2.0
        //Y: $PAGE->requires->js('/question/type/canvas/jquery-1.7.2.js');
        $mform->addElement('select', 'radius',
                get_string('radius', 'qtype_canvas'), array(
                    0 => 1,
                    1 => 3,
                    2 => 5,
                    3 => 7,
                    4 => 9,
                    5 => 11,
                    6 => 13,
                    7 => 15,
                    8 => 17,
                    9 => 19));

        $mform->addElement('select', 'usecase',
                get_string('casesensitive', 'qtype_canvas'), array(
                    0 => 50,
                    1 => 55,
                    2 => 60,
                    3 => 65,
                    4 => 70,
                    5 => 75,
                    6 => 80,
                    7 => 85,
                    8 => 90,
                    9 => 95,
                    10 => 100));

        /* Voma add end */
        /* Voma exclude start
           $menu = array(
           get_string('caseno', 'qtype_canvas'),
           get_string('caseyes', 'qtype_canvas')
           );
           $mform->addElement('select', 'usecase',
           get_string('casesensitive', 'qtype_canvas'), $menu);
           Voma exclude end */
        /* Voma start edit */





        // -----------
        // BAD UPLOAD
        // -----------
        //Y: $mform->addElement('static', 'drawsolution', get_string('correctanswers', 'qtype_canvas'),get_string('filloutoneanswer', 'qtype_canvas'));
        // -----------
        // GOOD UPLOAD
        // -----------
        $mform->addElement('filepicker', 'bgimage', get_string('bgimage', 'qtype_'.$this->qtype()),
                           null, self::file_picker_options());
        //$mform->addElement('filepicker', 'userfile', 'myFile', null,
        //                   array('maxbytes' => $maxbytes, 'accepted_types' => '*'));
        // -----------

        //Y: $mform->closeHeaderBefore('drawsolution');
        /* Voma end edit */

        $this->add_per_answer_fields($mform, get_string('answerno', 'qtype_canvas', '{no}'),
                question_bank::fraction_options());

        //Y: $this->add_interactive_settings();
        // --------------------------------
        $mform->addElement('header', 'hello world', 'hello world');
        // --------------------------------
        $mform->addElement('header', 'previewareaheader',
                get_string('previewareaheader', 'qtype_'.$this->qtype()));
        $mform->addElement('static', 'previewarea',
                get_string('previewarea', 'qtype_'.$this->qtype()),
                get_string('previewareamessage', 'qtype_'.$this->qtype()));

        $mform->registerNoSubmitButton('refresh');
        $mform->addElement('submit', 'refresh', get_string('refresh', 'qtype_'.$this->qtype()));
        $mform->closeHeaderBefore('refresh');

        list($itemrepeatsatstart, $imagerepeats) = $this->get_drag_item_repeats();
        $this->definition_drop_zones($mform, $imagerepeats);
        $mform->addElement('advcheckbox', 'shuffleanswers', ' ',
                get_string('shuffleimages', 'qtype_'.$this->qtype()));
        $mform->setDefault('shuffleanswers', 0);
        $mform->closeHeaderBefore('shuffleanswers');
        // Add the draggable image fields to the form.
        $this->definition_draggable_items($mform, $itemrepeatsatstart);

        $this->add_combined_feedback_fields(true);
        $this->add_interactive_settings(true, true);

    }
    
    protected function definition_drop_zones($mform, $imagerepeats) {
        $mform->addElement('header', 'dropzoneheader',
                           get_string('dropzoneheader', 'qtype_'.$this->qtype()));
        
        $mform->addElement('filepicker', 'bgimage', get_string('bgimage', 'qtype_'.$this->qtype()),
                           null, self::file_picker_options());
        
        $countdropzones = 0;
        if (isset($this->question->id)) {
            foreach ($this->question->options->drops as $drop) {
                $countdropzones = max($countdropzones, $drop->no);
            }
        }
        if ($this->question->formoptions->repeatelements) {
            $dropzonerepeatsatstart = max(self::START_NUM_ITEMS,
                                          $countdropzones + self::ADD_NUM_ITEMS);
        } else {
            $dropzonerepeatsatstart = $countdropzones;
        }
        
        $this->repeat_elements($this->drop_zone($mform, $imagerepeats), $dropzonerepeatsatstart,
                               $this->drop_zones_repeated_options(),
                               'nodropzone', 'adddropzone', self::ADD_NUM_ITEMS,
                               get_string('addmoredropzones', 'qtype_ddimageortext'), true);
                               
    }
    
    protected function get_drag_item_repeats() {
        $countimages = 0;
        if (isset($this->question->id)) {
            foreach ($this->question->options->drags as $drag) {
                $countimages = max($countimages, $drag->no);
            }
        }
        if ($this->question->formoptions->repeatelements) {
            $itemrepeatsatstart = max(self::START_NUM_ITEMS, $countimages + self::ADD_NUM_ITEMS);
        } else {
            $itemrepeatsatstart = $countimages;
        }
        $imagerepeats = optional_param('noitems', $itemrepeatsatstart, PARAM_INT);
        $addfields = optional_param('additems', '', PARAM_TEXT);
        if (!empty($addfields)) {
            $imagerepeats += self::ADD_NUM_ITEMS;
        }
        return array($itemrepeatsatstart, $imagerepeats);
    }
    
    public static function file_uploaded($draftitemid) {
        $draftareafiles = file_get_drafarea_files($draftitemid);
        do {
            $draftareafile = array_shift($draftareafiles->list);
        } while ($draftareafile !== null && $draftareafile->filename == '.');
        if ($draftareafile === null) {
            return false;
        }
        return true;
    }

    
    
    
    
    
    
    
    
    
    public function qtype() {
        return 'canvas';
    }

    public function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_combined_feedback($question, true);
        $question = $this->data_preprocessing_hints($question, true, true);

        $dragids = array(); // Drag no -> dragid.
        if (!empty($question->options)) {
            $question->shuffleanswers = $question->options->shuffleanswers;
            $question->drags = array();
            foreach ($question->options->drags as $drag) {
                $dragindex = $drag->no -1;
                $question->drags[$dragindex] = array();
                $question->drags[$dragindex]['draglabel'] = $drag->label;
                $question->drags[$dragindex]['infinite'] = $drag->infinite;
                $question->drags[$dragindex]['draggroup'] = $drag->draggroup;
                $dragids[$dragindex] = $drag->id;
            }
            $question->drops = array();
            foreach ($question->options->drops as $drop) {
                $question->drops[$drop->no -1] = array();
                $question->drops[$drop->no -1]['choice'] = $drop->choice;
                $question->drops[$drop->no -1]['droplabel'] = $drop->label;
                $question->drops[$drop->no -1]['xleft'] = $drop->xleft;
                $question->drops[$drop->no -1]['ytop'] = $drop->ytop;
            }
        }
        // Initialise file picker for bgimage.
        $draftitemid = file_get_submitted_draft_itemid('bgimage');

        file_prepare_draft_area($draftitemid, $this->context->id, 'qtype_ddimageortext',
                                'bgimage', !empty($question->id) ? (int) $question->id : null,
                                self::file_picker_options());
        $question->bgimage = $draftitemid;

        // Initialise file picker for dragimages.
        list(, $imagerepeats) = $this->get_drag_item_repeats();
        $draftitemids = optional_param_array('dragitem', array(), PARAM_INT);
        for ($imageindex = 0; $imageindex < $imagerepeats; $imageindex++) {
            $draftitemid = isset($draftitemids[$imageindex]) ? $draftitemids[$imageindex] :0;
            // Numbers not allowed in filearea name.
            $itemid = isset($dragids[$imageindex]) ? $dragids[$imageindex] : null;
            file_prepare_draft_area($draftitemid, $this->context->id, 'qtype_ddimageortext',
                                'dragimage', $itemid, self::file_picker_options());
            $question->dragitem[$imageindex] = $draftitemid;
        }
        if (!empty($question->options)) {
            foreach ($question->options->drags as $drag) {
                $dragindex = $drag->no -1;
                if (!isset($question->dragitem[$dragindex])) {
                    $fileexists = false;
                } else {
                    $fileexists = self::file_uploaded($question->dragitem[$dragindex]);
                }
                $labelexists = (trim($question->drags[$dragindex]['draglabel']) != '');
                if ($labelexists && !$fileexists) {
                    $question->dragitemtype[$dragindex] = 'word';
                } else {
                    $question->dragitemtype[$dragindex] = 'image';
                }
            }
        }
        $this->js_call();

        return $question;
    }


    public function js_call() {
        global $PAGE;
        $maxsizes =new stdClass();
        $maxsizes->bgimage = new stdClass();
        $maxsizes->bgimage->width = QTYPE_DDIMAGEORTEXT_BGIMAGE_MAXWIDTH;
        $maxsizes->bgimage->height = QTYPE_DDIMAGEORTEXT_BGIMAGE_MAXHEIGHT;
        $maxsizes->dragimage = new stdClass();
        $maxsizes->dragimage->width = QTYPE_DDIMAGEORTEXT_DRAGIMAGE_MAXWIDTH;
        $maxsizes->dragimage->height = QTYPE_DDIMAGEORTEXT_DRAGIMAGE_MAXHEIGHT;

        $params = array('maxsizes' => $maxsizes,
                        'topnode' => 'fieldset#previewareaheader');

        $PAGE->requires->yui_module('moodle-qtype_ddimageortext-form',
                                        'M.qtype_ddimageortext.init_form',
                                        array($params));
    }

    // Drag items.

    protected function definition_draggable_items($mform, $itemrepeatsatstart) {

        $this->repeat_elements($this->draggable_item($mform), $itemrepeatsatstart,
                $this->draggable_items_repeated_options(),
                'noitems', 'additems', self::ADD_NUM_ITEMS,
                get_string('addmoreimages', 'qtype_ddimageortext'));
    }

    protected function draggable_item($mform) {
        $draggableimageitem = array();

        $draggableimageitem[] = $mform->createElement('header', 'draggableitemheader',
                                get_string('draggableitemheader', 'qtype_ddimageortext', '{no}'));
        $dragitemtypes = array('image' => get_string('draggableimage', 'qtype_ddimageortext'),
                                'word' => get_string('draggableword', 'qtype_ddimageortext'));
        $draggableimageitem[] = $mform->createElement('select', 'dragitemtype',
                                            get_string('draggableitemtype', 'qtype_ddimageortext'),
                                            $dragitemtypes,
                                            array('class' => 'dragitemtype'));
        $draggableimageitem[] = $mform->createElement('filepicker', 'dragitem', '', null,
                                    self::file_picker_options());

        $grouparray = array();
        $grouparray[] = $mform->createElement('text', 'draglabel',
                                                get_string('label', 'qtype_ddimageortext'),
                                                array('size'=>30, 'class'=>'tweakcss'));
        $mform->setType('draglabel', PARAM_NOTAGS);
        $options = array();
        for ($i = 1; $i <= self::MAX_GROUPS; $i += 1) {
            $options[$i] = $i;
        }
        $grouparray[] = $mform->createElement('static', '', '', ' ' .
                get_string('group', 'qtype_gapselect').' ');
        $grouparray[] = $mform->createElement('select', 'draggroup',
                                                get_string('group', 'qtype_gapselect'),
                                                $options,
                                                array('class' => 'draggroup'));
        $grouparray[] = $mform->createElement('advcheckbox', 'infinite', ' ',
                get_string('infinite', 'qtype_ddimageortext'));
        $draggableimageitem[] = $mform->createElement('group', 'drags',
                get_string('label', 'qtype_ddimageortext'), $grouparray);
        return $draggableimageitem;
    }

    protected function draggable_items_repeated_options() {
        $repeatedoptions = array();
        $repeatedoptions['draggroup']['default'] = '1';
        return $repeatedoptions;
    }

    // Drop zones.

    protected function drop_zone($mform, $imagerepeats) {
        $dropzoneitem = array();

        $grouparray = array();
        $grouparray[] = $mform->createElement('static', 'xleftlabel', '',
                ' '.get_string('xleft', 'qtype_ddimageortext').' ');
        $grouparray[] = $mform->createElement('text', 'xleft',
                                                get_string('xleft', 'qtype_ddimageortext'),
                                                array('size'=>5, 'class'=>'tweakcss'));
        $mform->setType('xleft', PARAM_NOTAGS);
        $grouparray[] = $mform->createElement('static', 'ytoplabel', '',
                ' '.get_string('ytop', 'qtype_ddimageortext').' ');
        $grouparray[] = $mform->createElement('text', 'ytop',
                                                get_string('ytop', 'qtype_ddimageortext'),
                                                array('size'=>5, 'class'=>'tweakcss'));
        $mform->setType('ytop', PARAM_NOTAGS);
        $options = array();

        $options[0] = '';
        for ($i = 1; $i <= $imagerepeats; $i += 1) {
            $options[$i] = $i;
        }
        $grouparray[] = $mform->createElement('static', '', '', ' ' .
                                        get_string('draggableitem', 'qtype_ddimageortext').' ');
        $grouparray[] = $mform->createElement('select', 'choice',
                                    get_string('draggableitem', 'qtype_ddimageortext'), $options);
        $grouparray[] = $mform->createElement('static', '', '', ' ' .
                                        get_string('label', 'qtype_ddimageortext').' ');
        $grouparray[] = $mform->createElement('text', 'droplabel',
                                                get_string('label', 'qtype_ddimageortext'),
                                                array('size'=>10, 'class'=>'tweakcss'));
        $mform->setType('droplabel', PARAM_NOTAGS);
        $dropzone = $mform->createElement('group', 'drops',
                get_string('dropzone', 'qtype_ddimageortext', '{no}'), $grouparray);
        return array($dropzone);
    }

    protected function drop_zones_repeated_options() {
        $repeatedoptions = array();
        $repeatedoptions['xleft']['type']     = PARAM_INT;
        $repeatedoptions['ytop']['type']      = PARAM_INT;
        $repeatedoptions['choice']['default'] = '0';
        return $repeatedoptions;
    }

    public function validation($data, $files) {



        $errors = parent::validation($data, $files);
        $answers = $data['answer'];
        $answercount = 0;
        $maxgrade = false;
        foreach ($answers as $key => $answer) {
            $trimmedanswer = trim($answer);
            if ($trimmedanswer !== '') {
                $answercount++;
                if ($data['fraction'][$key] == 1) {
                    $maxgrade = true;
                }
            } else if ($data['fraction'][$key] != 0 ||
                    !html_is_blank($data['feedback'][$key]['text'])) {
                $errors["answer[$key]"] = get_string('answermustbegiven', 'qtype_canvas');
                $answercount++;
            }
        }
        if ($answercount==0) {
            $errors['answer[0]'] = get_string('notenoughanswers', 'qtype_canvas', 1);
        }
        if ($maxgrade == false) {
            $errors['fraction[0]'] = get_string('fractionsnomax', 'question');
        }
        return $errors;














        $errors = parent::validation($data, $files);
        if (!self::file_uploaded($data['bgimage'])) {
            $errors["bgimage"] = get_string('formerror_nobgimage', 'qtype_'.$this->qtype());
        }

        $allchoices = array();
        for ($i=0; $i < $data['nodropzone']; $i++) {
            $ytoppresent = (trim($data['drops'][$i]['ytop']) !== '');
            $xleftpresent = (trim($data['drops'][$i]['xleft']) !== '');
            $ytopisint = (string) clean_param($data['drops'][$i]['ytop'], PARAM_INT) === trim($data['drops'][$i]['ytop']);
            $xleftisint = (string) clean_param($data['drops'][$i]['xleft'], PARAM_INT) === trim($data['drops'][$i]['xleft']);
            $labelpresent = (trim($data['drops'][$i]['droplabel']) !== '');
            $choice = $data['drops'][$i]['choice'];
            $imagechoicepresent = ($choice !== '0');

            if ($imagechoicepresent) {
                if (!$ytoppresent) {
                    $errors["drops[$i]"] = get_string('formerror_noytop', 'qtype_ddimageortext');
                } else if (!$ytopisint) {
                    $errors["drops[$i]"] = get_string('formerror_notintytop', 'qtype_ddimageortext');
                }
                if (!$xleftpresent) {
                    $errors["drops[$i]"] = get_string('formerror_noxleft', 'qtype_ddimageortext');
                } else if (!$xleftisint) {
                    $errors["drops[$i]"] = get_string('formerror_notintxleft', 'qtype_ddimageortext');
                }

                if ($data['dragitemtype'][$choice - 1] != 'word' &&
                                        !self::file_uploaded($data['dragitem'][$choice - 1])) {
                    $errors['dragitem['.($choice - 1).']'] =
                                    get_string('formerror_nofile', 'qtype_ddimageortext', $i);
                }

                if (isset($allchoices[$choice]) && !$data['drags'][$choice-1]['infinite']) {
                    $errors["drops[$i]"] =
                            get_string('formerror_multipledraginstance', 'qtype_ddimageortext', $choice);
                    $errors['drops['.($allchoices[$choice]).']'] =
                            get_string('formerror_multipledraginstance', 'qtype_ddimageortext', $choice);
                    $errors['drags['.($choice-1).']'] =
                            get_string('formerror_multipledraginstance2', 'qtype_ddimageortext', $choice);
                }
                $allchoices[$choice] = $i;
            } else {
                if ($ytoppresent || $xleftpresent || $labelpresent) {
                    $errors["drops[$i]"] =
                            get_string('formerror_noimageselected', 'qtype_ddimageortext');
                }
            }
        }
        for ($dragindex=0; $dragindex < $data['noitems']; $dragindex++) {
            $label = $data['drags'][$dragindex]['draglabel'];
            if ($data['dragitemtype'][$dragindex] == 'word') {
                $allowedtags = '<br><sub><sup><b><i><strong><em>';
                $errormessage = get_string('formerror_disallowedtags', 'qtype_ddimageortext');
            } else {
                $allowedtags = '';
                $errormessage = get_string('formerror_noallowedtags', 'qtype_ddimageortext');
            }
            if ($label != strip_tags($label, $allowedtags)) {
                $errors["drags[{$dragindex}]"] = $errormessage;
            }

        }
        return $errors;
    }
}
