<?php
    /**
     * View for editing campaign.
     * 
     * @package Mailmaid
     * @subpackage views
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\views;

    class CampaignEditView extends View {
        /**
         * Contains data from the model of the view.
         * 
         * @var array[string|int] 
         */
        private $params;

        /**
         * Constructs parent class and sets value for the global $params.
         * 
         * @param array[string|int] $params
         */
        function __construct($params = array()) {   
            parent::__construct('Mailmaid | Campaigns', array('campaign.css', 'chart.css'), array('tinymce/tinymce.min.js', 'chart.js', 'ajax.js'), array('menu.js', 'link.js', 'campaignGraph.js'));
            $this->params = $params;
        }

        /**
         * Contains the HTML content to be displayed on the user.
         */
        function content() {
            echo "
                <script>
                    tinymce.init({
                        selector: '#message',
                        paste_data_images: true,
                        plugins: [
                            'legacyoutput advlist autolink lists link image charmap print preview hr anchor pagebreak',
                            'searchreplace wordcount visualblocks visualchars code fullscreen',
                            'insertdatetime media nonbreaking save table contextmenu directionality',
                            'paste textcolor colorpicker textpattern imagetools codesample toc help'
                        ],
                        toolbar1: 'insertfile undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
                        toolbar2: 'print preview media | forecolor backcolor | codesample help imageupload',
                        image_title: true,
                        images_upload_url: 'http://{$_SERVER['SERVER_NAME']}/mailmaid/campaign/upload',
                        images_upload_base_path: 'http://{$_SERVER['SERVER_NAME']}/mailmaid',
                        remove_script_host: false,
                        relative_urls: false,
                        file_picker_types: 'image',
                        automatic_uploads: true,
                        image_advtab: true,
                        file_picker_callback: function(cb, value, meta) {
                            var input = document.createElement('input');
                            input.setAttribute('type', 'file');
                            input.setAttribute('accept', 'image/*');

                            input.onchange = function() {
                                var file = this.files[0];
                                var reader = new FileReader();
                                reader.readAsDataURL(file);
                                reader.onload = function () {
                                    var id = 'blobid' + (new Date()).getTime();
                                    var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
                                    var base64 = reader.result.split(',')[1];
                                    var blobInfo = blobCache.create(id, file, base64);
                                    blobCache.add(blobInfo);

                                    cb(blobInfo.blobUri(), { title: file.name });
                                };
                            };
                            input.click();
                        }
                    });
                </script>
            ";
                        
            echo "
                <form class='content' action='http://{$_SERVER['SERVER_NAME']}/mailmaid/campaign/edit' method='post'>
                    <div class='main'>
                        <textarea name='content' id='message'>
                            {$this->params['content']}
                        </textarea>
                    </div>
                    <div class='sidebar'>
            ";
            
            switch($this->params['status']) {
                case 1:
                    echo "<p class='status blue'>Status: Ready</p>";
                    break;
                case 2:
                    echo "<p class='status yellow'>Status: Ongoing</p>";
                    break;
                case 3:
                    echo "<p class='status red'>Status: Paused</p>";
                    break;
                case 4:
                    echo "<p class='status green'>Status: Finished</p>";
                    break;
            }
                         
            echo "
                        <input name='token' type='hidden' value='{$this->params['token']}'>
                        <input name='campaignId' type='hidden' value='{$this->params['id']}'>
                        <label for='campaign'>Campaign Name</label>
                        <input name='campaign' placeholder='Campaign Name' value='{$this->params['name']}' id='campaign' type='text' required>
                        <label for='subject'>Subject</label>
                        <input value='{$this->params['subject']}' name='subject' placeholder='Subject' id='subject' type='text' required>
                        <label for='list'>List</label>
            ";
            
            if ($this->params['status'] != 1) {
                echo "<select name='list' id='list' disabled>";
            } else {
                echo "<select name='list' id='list'>";
            }
            
            foreach($this->params['list'] as $list) {
                if ($list['id'] == $this->params['listId']) {
                    echo "<option value='{$list['id']}' selected>{$list['name']}</option>";
                } else {
                    echo "<option value='{$list['id']}'>{$list['name']}</option>";
                }
            }
            
            echo "</select>";
            
            switch($this->params['status']) {
                case 1:
                    echo "
                    <input name='save' class='blue' type='submit' value='Save'>
                    <input name='operate' class='green' type='submit' value='Start'>";
                    break;
                case 2:
                    echo "
                    <input onclick=\"return confirm('Are you sure you want to edit content? This will not affect sent mails.');\" name='save' class='blue' type='submit' value='Save'>
                    <input name='operate' class='green' type='submit' value='Pause'>";
                    break;
                case 3:
                    echo "
                    <input onclick=\"return confirm('Are you sure you want to edit content? This will not affect sent mails.');\" name='save' class='blue' type='submit' value='Save'>
                    <input name='operate' class='green' type='submit' value='Resume'>";
                    break;
            }
            
            echo "
                        <input onclick=\"return confirm('Are you sure you want to delete this campaign?');\" name='delete' class='red' type='submit' value='Delete'>
                    </div>
                </form>
            ";
            
            echo "
                <div class='clearfix'></div>
                <p class='progress'>Target: {$this->params['progress']['target']} Mails | Sent: {$this->params['progress']['sent']} ({$this->params['progress']['sentPercent']}%) | Failed: {$this->params['progress']['failed']} ({$this->params['progress']['failedPercent']}%)</p>
                <div id='links' class='links'>
                    <p class='title'>Traceable Links</p>
                    <div class='header'>
                        <span>Link</span>
                        <span>Direction</span>
                        <span>Operation</span>
                    </div>
            ";
            
            foreach($this->params['links'] as $link) {
                echo "
                    <div id='link-{$link['id']}' class='link'>
                        <span>{$link['link']}</span>
                        <span>{$link['direction']}</span>
                        <span>
                            <button type='button' id='{$link['id']}'>Delete</button>
                        </span>
                    </div>
                ";
            }
            
            echo "
                </div>
                <form class='createLink' onkeypress='return event.keyCode != 13;'>
                    <input placeholder='Link (http://www.sample.com/products?id=10)' id='link' type='url' pattern='https?://.+' title='Include http://'>
                    <input id='campaignId' type='hidden' value='{$this->params['id']}'>
                    <button id='createLink' type='button'>Create Traceable Link</button>
                </form>
                <p id='alert' class='alert disabled'>Please use a valid URL. It should include \"http://\" or \"https://\"</p>
            ";
            
            echo "
                <div class='canvas'>
                    <canvas id='campaignEngagement'></canvas>
                    <button id='ce-1' type='button'>Last 24 Hours</button>
                    <button id='ce-2' type='button'>Last 7 Days</button>
                    <button id='ce-3' type='button'>This Year</button>
                </div>
            ";
            
            echo "
                <div class='canvas'>
                    <canvas id='mailSent'></canvas>
                    <button id='ms-1' type='button'>Last 24 Hours</button>
                    <button id='ms-2' type='button'>Last 7 Days</button>
                    <button id='ms-3' type='button'>This Year</button>
                </div>
            ";
        }
    }
