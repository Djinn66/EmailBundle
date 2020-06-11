$(document).ready(function() {
    /**
     * Add modal div into the body
     */
    $('body').append(
        "<div class=\"modal fade\" id=\"modalForm\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"examplemodalLabel\"\n" +
        "         aria-hidden=\"true\">\n" +
        "        <div class=\"modal-dialog modal-lg\" role=\"document\">\n" +
        "            <div class=\"modal-content\"id=\"modal-content\"></div>\n" +
        "        </div>\n" +
        "    </div>");

    $('.i-checks').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green',
    });

});


function loadModal() {

    // $('body').on('click', '.modal-toggle', function (event) {
    //     event.preventDefault();
    //     $('.modal-content').empty();
    //     $('#modalForm')
    //         .removeData('bs.modal')
    //         .modal({remote: $(this).attr('href') });
    // });

    /**
     * set focus to form-btn
     */
    $('.form-btn').focus();
    console.log("v3");

    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
    // Email tags input

    /**
     *  TAB 	9       ENTER   13     SPACE    32      !	33      "	34      #	35
     *  $	    36      %	    37      &	    38      '	39      (	40      )	41
     *  *	    42      ,	    44      /	    47      :	58      ;	59      <	60
     *  =       61      >       62      ?       63      @   64      [   91      \   92
     *  ]	    93      ^       94      `       96      {   123     |   124     }   125
     *  ~       126
     * @type {number[]}
     */
    let tagsConfirmKeys = [13, 32, 44, 59];

    let forbiddenKeys = {
        "!" : 33, "\"" : 34, "#" : 35, "$" : 36, "%" : 37, "&" : 38, "'" : 39,
        "(" : 40, ")"  : 41, "*" : 42, "/" : 47, ":" : 58, "<" : 60, "=" : 61,
        ">" : 62, "?"  : 63, "[" : 91, "\\": 92, "]" : 93, "^" : 94, "`" : 96,
        "{"  : 123,"|" : 124,"}" : 125
    }

    /**
     * disable "enter" action in the form when form-btn is unfocused
     */
    $('form').on('keypress', function(e) {
        let keyCode = e.keyCode || e.which;
        if (keyCode === 13 && !document.activeElement.classList.contains("form-btn")) {
            e.preventDefault();
            return false;
        }
    });

    // /**
    //  * focus form-btn when other inputs are not
    //  */
    // $('#modalForm').on('focus', function(){
    //     $('.form-btn').focus();
    // });

    /**
     *  tagsinput configs
     */
    let tags_text = $('.emailAddressTags');
    tags_text.tagsinput({
        typeahead: {
            source: $.getJSON("../api/get/users", function(data){
                return data;
            }),
        },
        tagClass: 'label label-primary',
        trimValue: true,
        confirmKeys: tagsConfirmKeys,
        cancelConfirmKeysOnEmpty: true,
    });

    /**
     * Clean text area after a tag is added
     * then hide keypress if is a forbidden key or a confirm key
     */
    let bootstrapTagsinputText  = $('.bootstrap-tagsinput input');
    bootstrapTagsinputText.on('change', function () {
        $(this).val("");
    }).on('keypress', function (e) {
        let hiddenKey = tagsConfirmKeys.includes(e.keyCode) || e.keyCode > 126;

        for(let key in forbiddenKeys){
            if (e.keyCode === forbiddenKeys[key]){
                hiddenKey = true;
            }
        }
        if (hiddenKey) {
            e.preventDefault();
        }

    });

    /**
     * check the email format before added a tag
     * then check if the email is already in the recipients
     */
    tags_text.on('beforeItemAdd', function(event) {
        let expressionReguliere = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;

        let recipients = $('#mail_recipients').val();
        let carbonCopyRecipients = $('#mail_carbon_copy_recipients').val();
        let blindCarbonCopyRecipients = $('#mail_blind_carbon_copy_recipients').val();
        let allRecipients = recipients + carbonCopyRecipients + blindCarbonCopyRecipients;
        event.item
        if (!expressionReguliere.test(event.item))
        {
            event.cancel = true;
            alertModal(event.item+" n'est pas une adresse email valide!");
        } else if (allRecipients.includes(event.item)){
            event.cancel = true;
            alertModal(event.item+" est déjà dans les destinataires!");
        }
    });

    $("#btn-cc-bcc").on("click", function () {
        let $icon = $(this).find("i.fa");
        if ($("#collapseOne").is(":visible")) {
            $icon.removeClass("fa-chevron-up");
            $icon.addClass("fa-chevron-down");
        } else {
            $icon.removeClass("fa-chevron-down");
            $icon.addClass("fa-chevron-up");
        }
    });

    // end Email tags input

    // Attachments
    /**
     * adjust the #progress-attachment width
     */
    let $btnAttachment = $("#btn-attachment");
    let $btnWidth = $btnAttachment.outerWidth();
    $("#progress-attachment").width($btnWidth);
    // end attachments

    let aSummernoteImages = [];
    // html content
    /**
     * summernote configs
     */
    $('.summernote').summernote({
        dialogsInBody: true,
        disableDragAndDrop: true,
        tabsize: 2,
        //height: 120,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen'/*, 'codeview'*/, 'help']]
        ],
        callbacks: {
            onImageUpload: function(files) {
                let data = new FormData();
                data.append("file", files[0]);
                $.ajax({
                    data: data,
                    type: "POST",
                    url: "../api/add/summernote/img",
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(response){
                        $('.summernote').summernote("insertImage", "../../../uploads/"+ response.file);
                    },
                    error: function (error) {
                        console.log(error);
                    }
                })
            },
            onChange: function(content){
                let aTempImages = content.match(/<img\s(?:.+?)>/g);
                let aTempSummernoteImages = aTempImages === null?[]:aTempImages;

                if (aSummernoteImages !== null && aTempSummernoteImages === null){
                    console.log(aSummernoteImages);

                }else if (aTempSummernoteImages.length < aSummernoteImages.length){
                    console.log(aSummernoteImages.filter(function(elm){
                        return aTempSummernoteImages.indexOf(elm) === -1;
                    }));

                }
                aSummernoteImages = aTempSummernoteImages;
            }
        },

    });

    /**
     * set focus on modalForm after closing a summernote modal (img, link, help...)
     */
    $(document).on("hidden.bs.modal", '.modal', function (event) {
        if ($(".modal:visible").length > 0){
            $("body").addClass("modal-open");
        }
        // console.log(event);
        // event.preventDefault();
        // $('.modal-content').empty();
        // $(this)
        //     .removeData('bs.modal')
        //     /*.modal({remote: $(this).attr('href') })*/;
        //$(this).removeData('bs.modal');
    });

    /**
     * put noteEditor as a form-control style
     * and hide the note-btn when is focus an other input
     */
    let noteEditor = document.querySelector('.note-editor');
    let noteToolbar = document.querySelector('.note-toolbar');

    noteToolbar.style.visibility = 'hidden';
    noteToolbar.style.height = '0px';

    $('.note-editable')
        .on('focus', function () {
            noteToolbar.style.visibility = 'visible';
            noteToolbar.style.height = 'auto';
            noteEditor.style.borderColor = '#1ab394';
        });
    $('input').on('focus', function () {
        noteToolbar.style.visibility = 'hidden';
        noteToolbar.style.height = '0px';
        noteEditor.style.borderColor = '#e5e6e7';
        let parent = this.parentElement;
        if (parent.className === "bootstrap-tagsinput"){
            parent.style.borderColor = '#1ab394';
        }
    }).on('focusout', function () {
        let parent = this.parentElement;
        if (parent.className === "bootstrap-tagsinput"){
            parent.style.borderColor = '#e5e6e7';
        }
    });

    // end html content

}


/**
 * Add Alert message in the modal
 * @param messageToAlert
 */
function alertModal(messageToAlert) {
    $('.modal-content').prepend(
        "<div class=\"alert alert-danger alert-dismissable\">" +
        "<button aria-hidden=\"true\" data-dismiss=\"alert\" class=\"close\" type=\"button\">×</button>" +
        messageToAlert +
        "</div>")
}

