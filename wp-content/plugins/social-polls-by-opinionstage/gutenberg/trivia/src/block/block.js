import './style.scss';
import './editor.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { SelectControl, TextControl } = wp.components;
const { RichText } = wp.editor ;

var dropdownOptions   = false;
var insertItemImage   = false;
var insertItemOsTitle = false;
var insertItemOsView  = false;
var insertItemOsEdit  = false;
var insertItemOsStatistics = false;

    registerBlockType( 'opinion-stage/block-os-trivia', {
        title: __( 'Trivia Quiz' ), 
        icon: 'yes',
        category: 'opinion-stage',        
        keywords: [
            __( 'Opinion Stage Trivia Quiz' ),
            __( 'Opinion Stage Trivia Quiz Insert' ),
        ],
        attributes: {
            embedUrl: {
                source: 'attribute',
                attribute: 'data-test-url',
                selector: 'div[data-test-url]'
            },            
            lockEmbed: {
                source: 'attribute',
                attribute: 'data-lock-embed',
                selector: 'div[data-lock-embed]'
            },
            buttonText: {
                source: 'attribute',
                attribute: 'data-button-text',
                selector: 'div[data-button-text]'
            },
            insertItemImage: {
                source: 'attribute',
                attribute: 'data-image-url',
                selector: 'div[data-image-url]'
            },
            insertItemOsTitle: {
                source: 'attribute',
                attribute: 'data-title-url',
                selector: 'div[data-title-url]'
            },
            insertItemOsView: {
                source: 'attribute',
                attribute: 'data-view-url',
                selector: 'div[data-view-url]'
            },
            insertItemOsEdit: {
                source: 'attribute',
                attribute: 'data-edit-url',
                selector: 'div[data-edit-url]'
            },
            insertItemOsStatistics: {
                source: 'attribute',
                attribute: 'data-statistics-url',
                selector: 'div[data-statistics-url]'
            }
        },
        edit: function( props ) {
            // Setting Attributes
            let {attributes: {embedUrl, lockEmbed, buttonText, insertItemImage,insertItemOsTitle,insertItemOsView,insertItemOsEdit,insertItemOsStatistics}, setAttributes} = props;

            // Fetching Localized variables
            var getCallBackUrlOs = osGutenData.callbackUrlOs;
            var callback_url = getCallBackUrlOs;
            var formActionUrlOS = osGutenData.getActionUrlOS;
            var getlogoImageLinkOs = osGutenData.getLogoImageLink;

            // Select Button Click functionality
            const onSelectButtonClick = value => {
                window.verifyOSInsert = function(widget){
                    props.setAttributes({ embedUrl: widget, buttonText:'Change' });

                    var opinionStageWidgetVersion = osGutenData.OswpPluginVersion;
                    var opinionStageClientToken = osGutenData.OswpClientToken;
                    var opinionstageFetchDataUrl = osGutenData.OswpFetchDataUrl+'?type=trivia&page=1&per_page=99';
                    fetch(opinionstageFetchDataUrl, {
                        method: "GET",
                        headers: {
                            'Accept':'application/vnd.api+json',
                            'Content-Type':'application/vnd.api+json',
                            'OSWP-Plugin-Version':opinionStageWidgetVersion,
                            'OSWP-Client-Token': opinionStageClientToken
                        },
                    })
                    .then(async res => {
                        var data = await res.json();
                        data = data.data;
                        dropdownOptions = data;
                        // force reprinting instead!!
                        props.setAttributes({ buttonText: buttonText});
                            
                    })
                    .catch(function(err) {
                        console.log('ERROR: ' + err.message);
                    });
                }
            }

            // Change Button Click functionality
            const onChangeButtonClick = value => {
                    props.setAttributes({ 
                        embedUrl: '', 
                        buttonText:'Embed', 
                        lockEmbed: false, 
                        insertItemImage: false, 
                        insertItemOsTitle: false, 
                        insertItemOsView: false,
                        insertItemOsEdit: false,
                        insertItemOsStatistics: false
                    });
                }

            // Connect to Opinionstage Callback Url
            const onConnectOSWPButtonClick = value => {
                window.location.replace(callback_url);
            };

            // Create New Item Url (trivia)
            var getOsCreateButtonClickUrl = osGutenData.onCreateButtonClickOs+'?w_type=quiz&amp;utm_source=wordpress&amp;utm_campaign=WPMainPI&amp;utm_medium=link&amp;o=wp35e8';
            const onCreateButtonClick = value => {
                // Open Create new trivia link in new page
                window.open(getOsCreateButtonClickUrl, '_blank').focus();
            };
            
            // Checking for Opinion Stage connection
            if(osGutenData.isOsConnected == ''){
            // Not Connected to opinionstage
                return (
                    <div className={ props.className }>
                        <div className="os-trivia-wrapper components-placeholder">
                            <p className="components-heading"><img src={getlogoImageLinkOs} alt=""/></p>
                            <p className="components-heading">Please connect WordPress to Opinion Stage to start adding trivia</p>
                            <button className="components-button is-button is-default is-block is-primary" onClick={onConnectOSWPButtonClick}>Connect</button>
                        </div>    
                        <div></div>      
                    </div>
                );
            }else{
            // Connected to opinionstage
                $(document).ready(function () {
                    // Content Popup Launch Working
                    $('span#oswpLauncherContentPopuptrivia').live('click', function(e) {
                        e.preventDefault();
                        setTimeout(function(){$('.editor-post-save-draft').trigger('click');},500);
                        var text = $(this).attr('data-os-block');
                        $("button#dropbtn span").text(text);   
                        var inputs = $(".filter__itm");   
                        for(var i = 0; i < inputs.length; i++){
                            if($(inputs[i]).text() == text){ 
                                setTimeout(function(){
                                    $(inputs[i]).trigger('click');
                                },1000);  
                                setTimeout(function(){
                                    $('.progress_message').css('display', 'none');
                                    $('.content__list').css('display', 'block'); 
                                },2500);                                                           
                                $('button.content__links-itm').live('click', function(e) {
                                    $('.tingle-modal.opinionstage-content-popup').hide();
                                    $('.tingle-modal.opinionstage-content-popup.tingle-modal--visible').hide();
                                }); 
                                break;  
                            }
                            else {
                                $('.progress_message').css('display', 'block');
                                $('.content__list').css('display', 'none');
                            }
                        }
                    });               
                });

                // Fetching Ajax Call Result
                if(dropdownOptions != false){
                    for (var i = 0; i < dropdownOptions.length; i++) {
                        var getLandingPageUrlOs = function(href) {
                        var locationUrlOS = document.createElement("a");
                        locationUrlOS.href = href;
                        return locationUrlOS;
                    };
                    var locationUrlOS = getLandingPageUrlOs(dropdownOptions[i].attributes['landing-page-url']);
                    var matchValue = locationUrlOS.pathname;   
                        if(embedUrl == matchValue){                                                           
                            props.setAttributes({lockEmbed: true, buttonText: "Change" });
                            props.setAttributes({ insertItemImage         : dropdownOptions[i].attributes['image-url'] });
                            props.setAttributes({ insertItemOsTitle       : dropdownOptions[i].attributes['title'] });
                            props.setAttributes({ insertItemOsView        : dropdownOptions[i].attributes['landing-page-url'] });
                            props.setAttributes({ insertItemOsEdit        : dropdownOptions[i].attributes['edit-url'] });
                            props.setAttributes({ insertItemOsStatistics  : dropdownOptions[i].attributes['stats-url'] });
                            break;                                
                        }                          
                    }
                }

                // Content On Editor
                var contentViewEditStatOs = (
                            <div className="os-trivia-wrapper components-placeholder">
                                <p className="components-heading"><img src={getlogoImageLinkOs} alt=""/></p>                        
                                <span id="oswpLauncherContentPopuptrivia" className="components-button is-button is-default is-block is-primary" data-opinionstage-content-launch data-os-block="trivia quiz" onClick={onSelectButtonClick} >Select a Trivia Quiz</span>
                                <input type="button" value="Create a New Trivia Quiz" className="components-button is-button is-default is-block is-primary" onClick={onCreateButtonClick} />
                                <span></span>
                            </div>       
                        );

                if(embedUrl != '' && embedUrl){
                    if(buttonText == 'Embed'){ 
                        contentViewEditStatOs
                    }else if(buttonText == 'Change'){                        
                        contentViewEditStatOs = (
                                <div className="os-trivia-wrapper components-placeholder"> 
                                    <p className="components-heading"><img src={getlogoImageLinkOs} alt=""/></p>                       
                                    <div className="components-preview__block" >                            
                                        <div className="components-preview__leftBlockImage">
                                            <img src={insertItemImage} alt={insertItemOsTitle} className="image" />
                                            <div className="overlay">
                                            <div className="text">
                                                <a href={insertItemOsView} target="_blank"> View </a>
                                                <a href={insertItemOsEdit} target="_blank"> Edit </a>
                                                <a href={insertItemOsStatistics} target="_blank"> Statistics </a>
                                                <input type="button" value={buttonText} className="components-button is-button is-default is-large left-align" onClick={onChangeButtonClick}/>
                                            </div>                                            
                                        </div>
                                    </div>
                                    <div className="components-preview__rightBlockContent">
                                        <div className="components-placeholder__label">Trivia Quiz: {insertItemOsTitle}</div>      
                                    </div>
                                </div>
                                <span></span>
                                </div>
                        );                           
                    }
                }else if(embedUrl == '' || jQuery.type(embedUrl) === "undefined"){
                    contentViewEditStatOs
                }else{  
                    props.setAttributes({ buttonText: 'Embed'});
                        contentViewEditStatOs 
                }
                return (
                    <div className={ props.className }>                                                             
                        {contentViewEditStatOs}
                        <span></span> 
                    </div>
                );
            }
        },
        save: function( {attributes: {embedUrl, lockEmbed, buttonText, insertItemImage, insertItemOsTitle, insertItemOsView, insertItemOsEdit, insertItemOsStatistics }} ) {
            return (
                <div class="os-trivia-wrapper"  data-type="trivia" data-image-url={insertItemImage} data-title-url={insertItemOsTitle} data-view-url={insertItemOsView} data-statistics-url={insertItemOsStatistics}  data-edit-url={insertItemOsEdit}  data-test-url={embedUrl} data-lock-embed={lockEmbed} data-button-text={buttonText}>
                    [os-widget path="{embedUrl}"]
                    <span></span>
                </div>
            );
        },
    } );