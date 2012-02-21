// calendar.js GLOBAL VARIABLES 

var calendar=false; // to ensure that the calendar is not initialized unnecessary
var dates=[];
var classes=[];
var data="";
var accord;
var datePicker;
var prevselecteddate,curselecteddate;
var weeks=['First','Second','Third','Fourth','Fifth'];
var days =['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];/*** TODO make this variable localized - not all countries have the same weekends. ***/
var longdays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];    															
var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
var activetabs = ['month','week','day'];
var weeknumbers = [];
var prevdate="";
var prevmonth;
var flag=0;
var activetab="";
var all=false;
var dpopen=false;

   /*IE Functions [START]*/
    
      /**
      * IE Alternative to the native JS function indexOf
      * @param  : mixed Element whose index is to be found
      * @param  : array The array to be searched
      * @return : int Index of the element(if found) | -1 (if not found)
      * @author : Anurup Raveendran
      */
      function index_Of(elt,array){
          for(i in array){
              if(array[i]===elt)
                  return i;
           }
           return -1; // not found
      }
      
      /**
      * Function to return the proper year
      * @param  : int Year to be checked
      * @return : int Proper year
      * @see    : getWeek()
      * @author : Anurup Raveendran
      */
      function y2k(number) { 
          return (number < 1000) ? number + 1900 : number; 
      }
      
      /**
      * IE Alternative for getting the Week number
      * @param  : int Year
      * @param  : int Month
      * @param  : int Day
      * @return : int Week number
      * @author : Anurup Raveendran
      */
      function getWeek(year,month,day) {
 
          var when = new Date(year,month,day);
          var newYear = new Date(year,0,1);
          var offset = 7 + 1 - newYear.getDay(); 
          if (offset == 8) offset = 1;
          var daynum = ((Date.UTC(y2k(year),when.getMonth(),when.getDate(),0,0,0) - Date.UTC(y2k(year),0,1,0,0,0)) /1000/60/60/24) + 1;
          var weeknum = Math.floor ((daynum-offset+7)/7);  
          if (weeknum == 0) {
               year--;
               var prevNewYear = new Date(year,0,1);
               var prevOffset = 7 + 1 - prevNewYear.getDay();
               if (prevOffset == 2 || prevOffset == 8) weeknum = 53; 
               else weeknum = 52;
          }
          return weeknum;
      }
      
      /**
      * Returns the date based on the php date format [IE Alternative]
      * @param  : string Date format
      * @param  : int Unix time in seconds                            
      * @return : string Formatted date
      * @author : Anurup Raveendran
      */ 
      function phpdate(format,unixts){
          var dobj = new Date(unixts*1000);
          var f = format.split(' ');
          var output="";
          for(i in f){
              switch(f[i]){
                  case "D" : output+=days[dobj.getDay()]+' ';
                             break;
                        
                  case "M" : output+=months[dobj.getMonth()]+' ';
                             break;
                        
                  case "j" : output+=dobj.getDate()+' ';
                             break;
                        
                  case "Y" : output+=dobj.getFullYear();
                             break;
                        
                  default  : break;
    	      }	
          }
          return output;
      }
     
     /* IE Functions [END] */
     
      /**
      * Returns the last date of the current month from the calendar
      * @param  : none
      * @return : string Last date of the current month
      * @author : Anurup Raveendran
      */
      function getlastdate(){    
          var last = 0;
          $('td.current-month').each(function(){                 
              last = $(this).html();
          });
          return last;
      }
       
      /**
      * Handles the hiding and showing of events
      * @param  : none                            
      * @return : none
      * @author : Anurup Raveendran
      */      
      $('.detailsbutton').live('click',function(){             
           var el;
              
           if(flag==0){
           
               el = $(this).parent('.detailsborder').next('ul').children('.hide');	 
               $(el).each(function(){
                   $(this).removeClass('hide').addClass('show');
               });
               $(this).addClass('minus');
               $(this).html("-");                           
           }
           else{ 
               el = $(this).parent('.detailsborder').next('ul').children('.show');
               $(el).each(function(){
                   $(this).removeClass('show').addClass('hide');
               });
    		   $(this).removeClass('minus');
               $(this).html("+");            
           }
           flag=!flag; 
      });
    
      /**
      * Parses the JSON data obtained via AJAX and stores them in an array.
      * The array is used by the function  appendContent
      * @param  : json object
      * @return : boolean just to make sure that the calendar is made only after the date object is available
      * @see    : appendContent()
      * @author : Anurup Raveendran
      */
      function datefunction(d){    
          var outer_index=0;
          var inner_index=0;
          var obj = $.parseJSON(d);
          for( i in obj){ 
              //get the length of each object 
              var l =   obj[i].length;
              for(j=0;j<l;j++){
          
          	      var il = obj[i][j].length;	      	
          	      for(k=0;k<il;k++){
          	          dates[outer_index+inner_index]=obj[i][j][k];	      	
          	          inner_index++;
          	      }
                  outer_index++;
              }
          }
          return true;
      } 
     
      /**
      * Appends content to the datecontent div.
      * This content serves as the "content storage" for the calendar as well as the accordion(views)
      * @param  : array Contains the date and the corresponding content      
      * @return : none
      * @author : Anurup Raveendran
      */    
      function appendContent(dateobj){
        
          var id,d,content,uuid,obj,time,unix;   
       
          d = dateobj['date'];
          if(d==undefined) return;
          content = dateobj['content'];
          uuid = dateobj['uuid'];            
          cl=dateobj['class'];
          unix=dateobj['unixts'];
          
          id = d.replace(/ /g,'');
          if($("#"+id).html()==null){
               var contentdiv="<div id='"+id+"'>"
                                +"<div class='"+uuid+"'>"
                                       +content+        	                            
                                       "<div class='contentclass'>"
                                          +cl+
        	                           "</div>"    		                                		                            
                                +"</div>"+
                              "</div>";
               $("#datecontent").append(contentdiv);
               $("#datecontent").html();
          }
          else{
              if($("."+uuid).html()==null){
                   var contentdiv="<div class='"+uuid+"'>"
                                      +content+        										    
                                     "<div class='contentclass'>"
                                        +cl+                                                    
                                     "</div>"+                                                    
                                  "</div>";
                   $("#"+id).append(contentdiv);    							
              }    
          }      
    }
    
     /**
     * Loads the month view for a particular date
     * @param  : jsdate
     * @param  : boolean Indicates whether to display the view or not
     * @return : none
     * @author : Anurup Raveendran
     */
     function monthView(dat,show){
         var active;
         if(show==undefined){
             show=false;
         }
         $("#monthul").html('');

         var muts = Math.round(dat.getTime() / 1000);
         muts = muts+'';
         
         var month1;
         if($.browser.msie){
             month1 = parseInt(dat.getMonth());
         }
         else{
             month1 = parseInt(date("m",muts));
         }
         
         var d = dat.toDateString();
         d = d.replace(/ /g,'');
         var otherdates = $("#datecontent div").not("#"+d);
         var montharray = [];
         var weekno;
         
         //initialize all the 4 weeks in the montharray
         for(j = 0 ; j < 5 ; j++){
             montharray[j] = [];
         }

         var week_var;
            if($.browser.msie){// IE Fix
                week_var = weeknumbers[parseInt(index_Of(month1,months))];
    		}
    		else{
    			week_var = weeknumbers[months.indexOf(month1)];
    		}
    		
            //first check if the week array is already created
            if(week_var==undefined){
                //construct the week array for the month
                weekno = [];

                var m = d.substr(3,3);//month
                var y = d.substr(8,4);//year
                // get 1st day and last day --- for that get the day of the currently selected date            

                var cdate = parseInt(d.substr(6,2));
                var cday = d.substr(0,3);            

                // 1st day

                var diff = parseInt(cdate - 1);    		
                var r = parseInt(diff%7);            

                var dayindex;
                
                if($.browser.msie){
    			    dayindex = parseInt(index_Of(cday,days))-r ;
    		    }
    		    else{
                    dayindex = days.indexOf(cday)-r;
                }
    		
                if(dayindex < 0){
                    dayindex+=7;
                }
                
                var fd = days[dayindex];

                // last day
                var ldate=parseInt(getlastdate());
                diff = parseInt(ldate - cdate);
                r = parseInt(diff%7);

                if($.browser.msie){
    			    dayindex=r+parseInt(index_Of(cday,days));
                }
    		    else{
    			    dayindex=r+days.indexOf(cday);	
                }
    			            
                if(dayindex > 6){
                    dayindex-=7;
                }
            
                var ld = days[dayindex];
            
        	    // get the corresponding week numbers
    		    var fw,lw,dt;
    		
    		    if($.browser.msie){
    			    dt = fd+' '+m+' '+'1'+' '+y;				
    			    fw = getWeek(y2k((new Date(dt)).getYear()),(new Date(dt)).getMonth(),(new Date(dt)).getDate());				
    			    dt = ld+' '+m+' '+ldate+' '+y;
    			    lw = getWeek(y2k((new Date(dt)).getYear()),(new Date(dt)).getMonth(),(new Date(dt)).getDate()); 
                }
    		    else{
                    // get the corresponding unix timestamps
                    dt = fd+' '+m+' '+'1'+' '+y;
    		        var futs = Math.round((new Date(dt)).getTime()/1000);
                    futs+='';
                    dt = ld+' '+m+' '+ldate+' '+y;
                    var luts = Math.round((new Date(dt)).getTime()/1000);
                    luts+='';
                    fw = parseInt(date("W",futs));
                    lw = parseInt(date("W",luts));
    		    }
        	    var index;
        	    for(index=fw;index <= lw;index++){
        	        weekno.push(index);
        	    }
        	
        	}
        	else{
    		    if($.browser.msie){			
    		        weekno = weeknumbers[parseInt(index_Of(month1,months))];
    		    }
        	    weekno = weeknumbers[months.indexOf(month1)];
        	}
        	
        	var week1;
    		if($.browser.msie){				
    		    week1 = getWeek(y2k(dat.getYear()),dat.getMonth(),dat.getDate());			
    		}
    		else{
    		    week1 = parseInt(date("W",muts));
    		}
    		
    		if($.browser.msie){
    			var ie = index_Of(week1,weekno);				
    			montharray[ie].push(muts);
    		}
    		else{
        		montharray[weekno.indexOf(week1)].push(muts);
    		}
        	
            $(otherdates).each(function(){
                    if($(this).attr('id')!=''){
                        var d2 = $(this).attr('id');
                        var i = 0;                        

                        d2 = d2.substr(0,3)+' '+d2.substr(3,3)+' '+d2.substr(6,2)+' '+d2.substr(8,4);
                        var uts2 = Math.round((new Date(d2)).getTime() / 1000);
                        uts2+='';                       
                        var month2 ;
    					if($.browser.msie){
    						 month2 = parseInt((new Date(d2)).getMonth());
    					}
    					else{
    						month2 = parseInt(date("m",uts2));
    					}
    					
                        if(month1==month2){
                            //check if it is in the same week as the current date
                            var week2;
    						if($.browser.msie){
    							week2 = getWeek(y2k((new Date(d2)).getYear()),(new Date(d2)).getMonth(),(new Date(d2)).getDate());
    						}
    						else{
    						    week2 = parseInt(date("W",uts2));
    						}                           
                           
                            if(week1 == week2 ){
                                if(uts2 < muts){
    							    if($.browser.msie){
    								    montharray[parseInt(index_Of(week2,weekno))].unshift(uts2);
    							    }
    							    else{
    								    montharray[weekno.indexOf(week2)].unshift(uts2); 
    							    }                            	
                                 }
                            }
                            else{
    							if($.browser.msie){
    								montharray[parseInt(index_Of(week2,weekno))].push(uts2);
    							 }
    							 else{
    								montharray[weekno.indexOf(week2)].push(uts2); 
    							 }
                            }     		  				
                        }
                }                
            });                    
       
            for ( i in montharray ){
    			
                var detailsbutton='';
                var hiddenclass='';
                var eventcount = 0;
                var uts;
                if(montharray[i]!=""){
                    var dayul="";
                    for(j in montharray[i]){    

                        if($.browser.msie){
                            uts = phpdate("D M j Y",montharray[i][j]);
                        }
                        else{
                            uts = date("D M j Y",montharray[i][j]);
    					}
     
                        var dayli = "<li class='"+hiddenclass+"'>"+uts+"</li>";
                        dayul+=dayli;
                        eventcount++;    
                        if(eventcount>8){
                            hiddenclass='hide';
                        }    	
                    }
                  
                    if(eventcount>8){
                        detailsbutton=  "<div class='detailsborder'><div class='detailsbutton'>+</div></div>";
                    }

                    var weekul = "<h5>"+weeks[i]+" Week</h5>"+detailsbutton+"<ul class='week' id='week"+i+"'>"+dayul+"</ul>";     
                    var monthli="<li>"+weekul+"</li>";     
                    $("#monthul").append(monthli);
    			}
           }  
           active = $(accord).accordion( "option", "active" );
      
           if(show){
               active = $(accord).accordion( "option", "active" );
               
               if(active!=0&&(typeof(active)=="number")){
                   $(accord).accordion( "option", "active", 0 );
               }
               else if(!active&&(typeof(active)=="boolean")){
                   $(accord).accordion( "option", "active", 0 );
               }               
               $(accord).accordion("resize");
               activetab="month";
           }
    }

     /**
     * Loads the week view for a particular date
     * @param  : jsdate
     * @param  : boolean Indicates whether to display the view or not)
     * @return : none
     * @author : Anurup Raveendran
     */     
     function weekView(dat,show){           
         var active;
    
         if(show==undefined){
             show=false;
         }
    
         $("#weekul").html('');

         var uts = Math.round(dat.getTime() / 1000);
         uts = uts+'';

         var week1 ;
         if($.browser.msie){
             week1 = getWeek(y2k(dat.getYear()),dat.getMonth(),dat.getDate());
    	 }
    	 else{
             week1 = parseInt(date("W",uts));    
    	 }
         var d = dat.toDateString();
         d = d.replace(/ /g,'');
         
         var otherdates = $("#datecontent div").not("#"+d);
         var day1 = d.substr(0,3);
         var month1=d.substr(3,3);
         var date1=parseInt(d.substr(6,2));
         var weekstart=false;
         var weekarray=[];
         weekarray.push(uts);
         
         $(otherdates).each(function(){
             if($(this).attr('id')!=''){

                 var d2 = $(this).attr('id');
                 d2 = d2.substr(0,3)
                      +' '
                      +d2.substr(3,3)
                      +' '
                      +d2.substr(6,2)
                      +' '
                      +d2.substr(9,4);
                 var uts2 = Math.round((new Date(d2)).getTime() / 1000);
                 uts2 = uts2 + '';
                 var week2;
                 if($.browser.msie){
    				 week2 = getWeek(y2k((new Date(d2)).getYear()),(new Date(d2)).getMonth(),(new Date(d2)).getDate());
    			 }
    			 else{
    		 		 week2 = parseInt(date("W",uts2));	
    			 }
               
                 if(week1==week2){
                     if(uts2 > uts){    		
                         weekarray.push(uts2);
                     }
                     else if(uts2 < uts){
                         weekarray.unshift(uts2);
                     }
                }    	
            }
        });
        
        if( weekarray.length > 0){
        var eventcount=0;
        var hiddenclass='';
            //append to the weekul from array
            for ( i in weekarray ){
              var cuts = weekarray[i];
    		  var cs;
    		  if($.browser.msie){
    			  cs = phpdate("D M j Y",cuts);
    		  }
    		  else{
    			  cs = date("D M j Y",cuts);
    		  }
              var dayli = "<li class='"+hiddenclass+"'>"+cs+"</li>";
                $("#weekul").append($(dayli));    
                eventcount++;
                if(eventcount>8){
                hiddenclass='hide';
                $('#weekul')
                .before("<div class='detailsborder'><div class='detailsbutton'>+</div></div>");
                }    	
            }
        }
        else{		
    		var ds;
    		if($.browser.msie){
    			phpdate("D M j Y",uts);
    		}
    		else{
    			ds = date("D M j Y",uts);
    		}
    	
        	var dayli = "<li>"+ds+"</li>";
        	$("#weekul").append($(dayli));			
        } 
        active = $(accord).accordion( "option", "active" );
         
        if(show){       
        	
        	if(active!=1&&(typeof(active)=="number")){
            	$(accord).accordion( "option", "active", 1 );
            }
            else if(!active&&(typeof(active)=="boolean")){
                    $(accord).accordion( "option", "active", 1 );
            }            
            $(accord).accordion("resize");    
            activetab="week";         
        }
    } 
    
     /**
     * Loads the day view for a particular date
     * @param    : jsdate
     * @param    : boolean Indicates whether to display the view or not
     * @return   : none
     * @author   : Anurup Raveendran     
     */     
     function dayView(date,show){
         var active;
         if(show==undefined){
             show=false;
         }
            	
         var d = date.toDateString();
         d = d.replace(/ /g,'');

         var content = $("#"+d).html();  
        
         var dayli = "<li>"+content+"</li>";
         $("#dayul").html(dayli);
        
         // check the content 
         var c = $("#dayul").children('li').children('span.day').hide();
        
         if(show){//activate
             // get the active tab
        	 active = $(accord).accordion( "option", "active" );
        	
        	 // only activate if the active tab is not the day tab
        	 if(active!=2&&(typeof(active)=="number")){
            	 $(accord).accordion( "option", "active", 2 );
             }
             else if(!active&&(typeof(active)=="boolean")){
                 $(accord).accordion( "option", "active", 2 );
             }
             $(accord).accordion("resize");
             activetab="day";
         }
         else{//deactivate
        	 active = $(accord).accordion( "option", "active" );
        	
        	 if(active&&(typeof(active)=="boolean")){
                   $(accord).accordion( "option", "active", null );
             }
             else if(active==2){
                  $(accord).accordion( "option", "active", false );
             }
             $(accord).accordion("resize");	   
        }
     }
    
     /**
     * Function for converting the content into fittable content for the tooltip
     * @param : string Content
     * @return : string Minified content
     * @author : Anurup Raveendran
     */     
     function getToolTipContent(content){
     
    	/*
    	  parse the content
    	  First get the number of divs  
    	  that number divided by 2 will give the number of events. 
    	  We will have to limit the content of the events 
    	*/
    	
    	var n = $('div',$(content)).size(); //number of divs
    	n=n/2; // number of events	
    	
    	/* 
    	 according to the ATutor Calendar Format
    	 the event heading will be the time of the event - [content of (span.time)]
    	 the event content - [content of (span.module + span.title + span.content)] 
    	*/
    	
    	var events,ttcontent;
    	events='<div class="events">';
    	events+='<ul>';		
    	
    	$('div.content',content).each(function(){   	
    		
    	    events+='<li>';
    	    events+='<span class="title">';
    	    events+= $(this).children('span.day').html()+"&nbsp;";
    		events+=$(this).children('span.time').html();
    		events+='</span>';
    		events+='<span class="desc">';					 			   	   
    	   	    var c  = $(this).children('span.content').html();
    	   	    var m = $(this).children('span.module').html();
    	   	    var t = ($(this).children('span.title').html()!=null)?$(this).children('span.title').html():"";
    	   	    events+='<span style="color:#C22;">'
    	   	    		+c+m+t
    	   	    		+'&nbsp;</span>';
    	   	events+='</span>';
    	   	events+='</li>';		
    	
    	});
    	events+='</ul>';
    	events+='</div>';
    			
     return events;
     }
          
     
     /**
     * Function for getting dates via AJAX using jQuery.ajax function
     * @param  : string URL of the dateretrieval script
     * @param  : string Data to be posted to the script
     * @param  : boolean Whether @function datefunction should return or not
     * @param  : string Ajax METHOD POST|GET 
     * @return : none
     * @see    : datefunction()
     * @author : Anurup Raveendran    
     */
    
    function ajax(ajaxurl,d,ret,method){
        if(method==undefined){
            method="POST";
        }
        var turn=false;
        $.ajax({
            type:method,
            url :ajaxurl,         
            data:d,
            success: function(msg){   
            if(ret){
                while(!turn){
                    turn=datefunction(msg);
                }    	 
        		/*
        		 * append content to contentdiv so that content is available for both the calendar and accordion
        		 */			 
        		 for(i in dates){	    		 
                 appendContent(dates[i],i);
        		 }
        	
        	 $('.date-pick').datePicker({
        			showOn: 'button',
        			createButton:false,	    			
        			closeOnSelect:false,
                    selectMultiple:false,        			
                    renderCallback:function($td, thisDate, month, year){    
                    
                        var d = thisDate.toDateString();
                        d = d.replace(/ /g,'');    
                        var dateel = "#"+d;
                        var content = $(dateel).html();
                       
                        if((content!=null)||((content==""))){ 
                            // LOAD THE TOOLTIP FOR EACH  RELEVANT DATE   	
                            content = getToolTipContent(content);
    					
    					    var html = $td.html();
    					    $td.html(html+content);
    					
    					    // options
    					    var distance = 10;
    					    var time = 250;
    					    var hideDelay = 300;

    					    var hideDelayTimer = null;

    					    // tracker
    					    var beingShown = false;
                            var shown = false;
    	    
                            var trigger = $td;
                            var popup = $('.events ul', $td).css('opacity', 0);

                            // set the mouseover and mouseout on both element
                            $([trigger.get(0), popup.get(0)]).mouseover(function () {
    					
                                if(!$.browser.msie){
    				                  if($td.is('.cellselected')) return;
    					        }
    					        
    					        // stops the hide event if we move from the trigger to the popup element
    					        if (hideDelayTimer) clearTimeout(hideDelayTimer);

    					        // don't trigger the animation again if we're being shown, or already visible
    					        if (beingShown || shown) {
    						        return;
    				 	        }
    				 	        else {
    					            beingShown = true;    					  
    					            left = $td.offset().left;
    					        
    					            if(left>625){
    						            var options = {bottom:20,right:(left/30),display:'block'};
    					            }
    					            else{
    						            var options = {bottom:20,left:(left/30),display:'block'};
    					            }
    					        
    					            // reset position of popup box
    					            popup
    					            .css(options)
    					    
    					            // (we're using chaining on the popup) now animate it's opacity and position
    					            .animate({    					       
    						             bottom: '+=' + distance + 'px',
    						             opacity: 1
    						            },
    						            time,
    						           'swing',
    						           function() {
    					                   // once the animation is complete, set the tracker variables
    					                   beingShown = false;
    					                   shown = true;
    					               }
    					             );
    			 	             }
    				         }).mouseout(function () {
    				
                                   // reset the timer if we get fired again - avoids double animations
    					           if (hideDelayTimer) clearTimeout(hideDelayTimer);

    					           // store the timer so that it can be cleared in the mouseover if required
                                   hideDelayTimer = setTimeout(function () {
    													hideDelayTimer = null;
    													popup.animate(
    													{
                                                            bottom: '-=' + distance + 'px',
                                                            opacity: 0
    													},  
    													time, 
    													'swing', 
    													function () {
    													    // once the animate is complete, set the tracker variables
    													    shown = false;
    													    // hide the popup entirely after the effect (opacity alone doesn't do the job)
    													    popup.css('display', 'none');
    													});
    					            },
    					            hideDelay);
    					     });

                             var cl = $(dateel)
                                      .find('.contentclass')
                                      .html();			     
                                	
                             $td.addClass('atutordate date_has_event '+cl);                    
    						
     					     if(month!=prevmonth){        							                    	
                                  monthView(thisDate,true);								
                                  weekView(thisDate,false);
                                  dayView(thisDate,false);
                                  prevmonth=month;								
                             }   							
    		            } 
    		        	else{
                            $td.addClass('disabled');
                        }
    		      }
    		})
    		.bind(
    			'click',
    			function(e, selectedDate,$td,state)				
    			{	
    				if(!dpopen){
    					$(this).dpDisplay();
    					this.blur();
    				}
    				return false;									
    			}
    		);

    		$('#dp').bind('focus', function() {			
    		    $('#dp').click();  		
    		    dpopen=true;
    	    });  
    			
            accord = $("#accord").accordion({active:false,
                                             clearStyle: true,
                                             navigation:true,
                                             collapsible:true
                   						    });	  
                   				
            //workaround to make sure the accord is under the calendar				
            $(accord).detach();
            $(accord).insertAfter($("#dp"));               
        
            $("#dp").trigger('focus');        	// load the calendar	    	
            	
            $("#dp-popup").css('left','-13em');
    	    $("#dp-popup").offset({top:254,left:390}); //for centering the calendar TODO - get a generic way of centering
    	 }		 
       $('#status').fadeOut(100);	
       }		 
     });
    }
    
     /**
     * Get the course relevant dates via AJAX     
     * @param  : none
     * @return : none
     * @author : Anurup Raveendran
     */
     function getCourseDates(){

    	 if(calendar){
             $('#status').fadeIn(500);
             var request = "request=getdates";
             if(all==true){
                 request+= '&all=true';
             }
             ajax(ATutor.base_href+'mods/calendar/dateretrieval.php',request,true); 
         }	        
     }
    
     /**
     * Calendar Module JS Initialization function
     * Called when the calendar.php is loaded
     * @param  : none
     * @return : none
     * @author : Anurup Raveendran
     */
     $(document).ready(function(){ 
    
         getCourseDates();        			            

         var top = $("#gototop");
         $(top).detach();
         $("#footer").prepend($(top));

         
         /**
         * Load the Day View when events in Week View or Month View are clicked
         * @param  : none
         * @return : none
         * @author : Anurup Raveendran
         */
         $(".week li,#weekul li").live('click',function(){
       	      var d = new Date($(this).text());
              dayView(d,true);
              weekView(d,false);
              monthView(d,false);  

         });
         
         /**
         * Load the corresponding view when the accordion tabs are clicked
         * @param  : none
         * @return : none
         * @author : Anurup Raveendran
         */      
         $('.accordhref').live('click',function(){
        
              var id = $(this).attr('id');	    
              if(activetab!=""){		    		    
                     if(id==activetab){	    		
                         $(accord).accordion("activate",null);
                         activetab="";
                     }
                     else{
                         $(accord).accordion("activate",activetabs.indexOf(id));
                         activetab=id;	    		
                     }	   
              }	
              else{
                  $(accord).accordion("activate",activetabs.indexOf(id));
                  activetab=id;
              }
          });   
          
          var region=null;
          var regionremoved;
          var toremove=null;
          
          /**
          * Load the corresponding timezones for a region 
          * @param  : none
          * @return : none
          * @author : Anurup Raveendran
          */
          $('#inpregion').change(function(){

    				region = $(this).val();
    				if (( regionremoved != region ) || ( region == null )){				
    					
    					if(toremove!=null){
    		 				$('#inptimezone').append($(toremove));
    					}
    					toremove =$('#inptimezone').children('option').not('.'+region);			
    					$(toremove).detach();			
    					regionremoved=region;
    				}		
    	   });
    });
