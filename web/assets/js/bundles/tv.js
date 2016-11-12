/**
 * Class that manipulate the TV web interface according
 * to instruction of the server
 * @param interval Interval of refresh in s
 * @param uri Uri where data will be downloaded
 */
function Tv(interval, uri) {
    this.uri = uri;
    this.interval = interval;
    this.positionsUpdate = new Date(0);
    this.EtuUTTVersion = false;

    /**
     * Executed to switch to the next subblock in a block on the sidebar
     */
    this.blockTransition = function(block) {
        var subblocks = block.children('div.tv-subblock');

        // Select new subblock
        var current = block.data('current') + 1;
        if (current >= subblocks.length) {
            current = 0;
        }
        block.data('current', current);


        // Hide and show the current block
        // subblocks.not(subblocks.eq(current)).hide('fast');
        // subblocks.eq(current).show('fast');
        subblocks.not(subblocks.eq(current)).hide()
        subblocks.eq(current).fadeIn(800);

        if (subblocks.length > 1) {
            // Set progressbar
            var duration = block.children('div.tv-subblock').eq(block.data('current')).data('duration');
            block.children('.tv-progress').css('display', 'block');
            block.children('.tv-progress').animate({
                width: block.parent().width()+'px'
            }, duration, function() {
                block.children('.tv-progress').css('width', '0px');
                that.blockTransition(block);
            })

            // Set page indicator
            var html = '';
            for (var i = 0; i < subblocks.length; i++) {
                if(i==current) {
                    html += '<i class="fa fa-circle" aria-hidden="true"></i> ';
                }
                else {
                    html += '<i class="fa fa-circle-thin" aria-hidden="true"></i> ';
                }
            }
            block.children('.tv-pageIndicator').css('display', 'block');
            block.children('.tv-pageIndicator').html(html);
        }
        else {
            block.children('.tv-progress').css('display', 'none');
            block.children('.tv-pageIndicator').css('display', 'none');
        }
    }

    this.update = function() {
        $.getJSON(this.uri, function(data) {

            // Refresh page on EtuUTT update
            if (that.EtuUTTVersion === false) {
                that.EtuUTTVersion = data.etuuttVersion;
            }
            else if (that.EtuUTTVersion != data.etuuttVersion) {
                location.reload();
            }


            // Update the whole side panel
            var serverPositionsUpdate = new Date(data.sidepanel.positionsUpdate);
            if (serverPositionsUpdate && that.positionsUpdate < serverPositionsUpdate) {

                // Clear transition loops
                var blocks = $('.tv-sidepanel').children('div.tv-block');
                blocks.each(function(){
                    $(this).html('');
                });

                // Create blocks without content
                that.positionsUpdate = serverPositionsUpdate;
                var html = '';
                var positions = data.sidepanel.positions;
                for (var positionI in positions) {
                    if (positions.hasOwnProperty(positionI)) {
                        html += '<div class="tv-block">'
                            + '<div class="tv-progress"></div>'
                            + '<div class="tv-pageIndicator"></div>';
                        for (positionJ in positions[positionI]) {
                            if (positions[positionI].hasOwnProperty(positionJ)) {
                                html += '<div class="tv-subblock tv-subblock-'+positions[positionI][positionJ]+'"></div>';
                            }
                        }
                        html += '</div>';
                    }
                }
                $('.tv-sidepanel').html(html);
            }

            // Update sidepanel subblock contents
            for (var index in data.sidepanel.block) {
                if (data.sidepanel.block.hasOwnProperty(index)) {
                    var block = data.sidepanel.block[index];
                    $('.tv-block > .tv-subblock-'+block.id).each(function() {
                        if(!$(this).data('update') || (new Date(block.update)) > $(this).data('update')) {
                            $(this).data('update', new Date(block.update));
                            $(this).html('<h4>'+block.title+'</h4>' + block.content);
                            $(this).data('duration', block.duration);
                        }
                    })
                }
            }

            // Create transition timeouts on side panels
            $('.tv-sidepanel').children('div').each(function () {
                var block = $(this);
                if(!block.data('transition')) {
                    block.data('current', -1);
                    block.data('transition', true);
                    that.blockTransition(block);
                }
            });

            // Update the main container
            var container = $('.tv-container');
            var serverOrderUpdate = new Date(data.main.orderUpdate);
            if (!container.data('update')
                || serverOrderUpdate && container.data('update') < serverOrderUpdate) {

                // Clear transition loops
                container.each(function(){
                    $(this).html('');
                });

                // Create blocks without content
                container.data('update', serverOrderUpdate);
                var html = '';
                for (var slide in data.main.slides) {
                    if (data.main.slides.hasOwnProperty(slide)) {
                        html += '<div class="tv-subblock tv-subblock-'+data.main.slides[slide].id+'"></div>';
                    }
                }
                html += '<div class="tv-progress"></div><div class="tv-pageIndicator"></div>';
                container.html(html);
            }

            // Update main container subblock contents
            for (var slide in data.main.slides) {
                if (data.main.slides.hasOwnProperty(slide)) {
                    var block = data.main.slides[slide];
                    $('.tv-container > .tv-subblock-'+block.id).each(function() {
                        if(!$(this).data('update') || (new Date(block.update)) > $(this).data('update')) {
                            $(this).data('update', new Date(block.update));
                            $(this).html(block.content);
                            $(this).data('duration', block.duration);
                        }
                    })
                }
            }

            // Create transition timeouts on main container
            if(!container.data('transition')) {
                container.data('current', -1);
                container.data('transition', true);
                that.blockTransition(container);
            }

            // Update textpanels
            var rightTextPanel = $('.tv-text-panel-right');
            var bottomTextPanel = $('.tv-text-panel-bottom');
            var serverRightTextUpdate = new Date(data.textpanelRight.update);
            var serverBottomTextUpdate = new Date(data.textpanelBottom.update);

            // Right textpanel
            if(!rightTextPanel.data('update')
                || (serverRightTextUpdate && rightTextPanel.data('update') < serverRightTextUpdate)) {
                if (data.textpanelRight.show) {
                    rightTextPanel.data('update', serverRightTextUpdate);
                    rightTextPanel.html(data.textpanelRight.content);
                    rightTextPanel.css('font-size', data.textpanelRight.fontsize);
                    rightTextPanel.css('text-align', data.textpanelRight.align);
                    rightTextPanel.css('display', 'block');
                }
                else {
                    rightTextPanel.css('display', 'none');
                }
            }

            // bottom textpanel
            if(!bottomTextPanel.data('update')
                || (serverRightTextUpdate && bottomTextPanel.data('update') < serverBottomTextUpdate)) {
                if (data.textpanelBottom.show) {
                    bottomTextPanel.data('update', serverBottomTextUpdate);
                    bottomTextPanel.html(data.textpanelBottom.content);
                    bottomTextPanel.css('font-size', data.textpanelBottom.fontsize);
                    bottomTextPanel.css('text-align', data.textpanelBottom.align);
                    bottomTextPanel.css('display', 'block');
                }
                else {
                    bottomTextPanel.css('display', 'none');
                }
            }

            // Update styles
            var bottomMargin = 0;
            if(data.textpanelBottom.show) {
                bottomMargin += $('.tv-text-panel-bottom').outerHeight();
            }
            $('.tv-container').css('height', $(window).height() - bottomMargin);
        })
    }

    // Construction
    if(!this.interval) {
        this.interval = 30;
    }
    var that = this;
    setInterval(this.update, this.interval*1000);
    this.update();

}
