var reddit_link_counter = 0;
var linkNames = [];
var linkThumbnails = [];
var linkPreviews = [];
var reddit_worker_delay = 2000; //delay for the worker in milliseconds.
var storemode = 1; //storemodes: 0 = do nothing. 1 = memory array. 2 = localstorage.
var reddit_str_store = [];//array for caching when using no local storage.
var kue = []; //queue of task objects

//check if something is in the cache.
//returns a boolean.
function cache_check(key)
{
  if( storemode == 1 )
  {//array
    if( reddit_str_store[ key ] == null || reddit_str_store[ key ] == "" )
      return false;
    else
      return true;
  }
  else if( storemode == 2 )
  {
    var x = localStorage.getItem( key );
    if( x == null || x == "" )
    {
      return false;
    }
    else
      return true;
  }
  //unknown store mode.
  return false;
}

//get something from the cache.  Returns whatever was stored (string usually).
function cache_get(key)
{
  if( storemode == 1 )
  {//array
    if( reddit_str_store[ key ] == null || reddit_str_store[ key ] == "" )
      return false;
    else
      return reddit_str_store[ key ];
  }
  else if( storemode == 2 )
  {
    var x = localStorage.getItem( key );
    if( x == null || x == "" )
      return false;
    else
      return x;
  }
  //unknown store mode
  return false;
}

//set or update something in the cache.
function cache_set(key, newValue)
{
  if( storemode == 1 )
  {//array
    reddit_str_store[ key ] = newValue;
  }
  else if( storemode == 2 )
  {
    localStorage.setItem( key, newValue );
  }
  else
    return false;
}

//worker heartbeat function
function worker()
{
  console.log("bang");
  setTimeout( worker, reddit_worker_delay );
}

//end worker

//puts <a href=$url> around urls.
function linkify(text)//http://stackoverflow.com/questions/6707476/how-to-find-if-a-text-contains-url-string
{
  var exp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
  return text.replace(exp,"<a href='$1'>$1</a>"); 
}
//linkifies URLs using a different regex. This one is more specific but doesn't deal with parenthesis in a satisfactory way.
function linkify2(text) {//http://stackoverflow.com/questions/1500260/detect-urls-in-text-with-javascript
    //var urlRegex = /(https?:\/\/[^\s]+)/g;
    var urlRegex = /((?:(http|https|Http|Https|rtsp|Rtsp):\/\/(?:(?:[a-zA-Z0-9\$\-\_\.\+\!\*\'\(\)\,\;\?\&\=]|(?:\%[a-fA-F0-9]{2})){1,64}(?:\:(?:[a-zA-Z0-9\$\-\_\.\+\!\*\'\(\)\,\;\?\&\=]|(?:\%[a-fA-F0-9]{2})){1,25})?\@)?)?((?:(?:[a-zA-Z0-9][a-zA-Z0-9\-]{0,64}\.)+(?:(?:aero|arpa|asia|a[cdefgilmnoqrstuwxz])|(?:biz|b[abdefghijmnorstvwyz])|(?:cat|com|coop|c[acdfghiklmnoruvxyz])|d[ejkmoz]|(?:edu|e[cegrstu])|f[ijkmor]|(?:gov|g[abdefghilmnpqrstuwy])|h[kmnrtu]|(?:info|int|i[delmnoqrst])|(?:jobs|j[emop])|k[eghimnrwyz]|l[abcikrstuvy]|(?:mil|mobi|museum|m[acdghklmnopqrstuvwxyz])|(?:name|net|n[acefgilopruz])|(?:org|om)|(?:pro|p[aefghklmnrstwy])|qa|r[eouw]|s[abcdeghijklmnortuvyz]|(?:tel|travel|t[cdfghjklmnoprtvwz])|u[agkmsyz]|v[aceginu]|w[fs]|y[etu]|z[amw]))|(?:(?:25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[1-9][0-9]|[1-9])\.(?:25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[1-9][0-9]|[1-9]|0)\.(?:25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[1-9][0-9]|[1-9]|0)\.(?:25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[1-9][0-9]|[0-9])))(?:\:\d{1,5})?)(\/(?:(?:[a-zA-Z0-9\;\/\?\:\@\&\=\#\~\-\.\+\!\*\'\(\)\,\_])|(?:\%[a-fA-F0-9]{2}))*)?(?:\b|$)/gi;
    return text.replace(urlRegex, function(url) {
        return '<a href="' + url + '">' + url + '</a>';
    })
}
//send an XHR to get more comments.
function loadMoreComments( link_id, sub, commentCSV, targetID)
{
  var url = "https://www.reddit.com/r/" + sub + "/api/morechildren.json?link_id=" + link_id + "&children=";
  url += commentCSV + "&api_type=json";
  var xh = new XMLHttpRequest();
  xh.onreadystatechange = function()
  {
    console.log( xh.readyState + " " + xh.status );
    if( xh.readyState == 4 )
    {
      if( xh.status == 200 )
      {
        obj = JSON.parse( xh.responseText );
        
        var str = ""
//          + xh.responseText 
            + "<ul>";
        for( var i = 0; i < obj.json.data.things.length; ++i )
        {
          str += "<li>" + parseCommentTree( obj.json.data.things[i] ) + "</li>";
        }
        str += "</ul>";
        document.getElementById(targetID).innerHTML = str;
      }
    }
  };
  document.getElementById(targetID).style.backgroundColor = "#ddd";
  xh.open("GET", url, true);
  xh.send(null);
}
//takes a JSON decoded comment tree and returns
//a string containing the html.
//sub field is the subreddit, used for more buttons.
function parseCommentTree( co, sub)
{ //or use body_html.
  var str = "";
  if( co.kind == 't1' )
  {
    var b = co.data.body;
    b = linkify( b );
    b = b.replace( /(?:\r\n|\r|\n)/g, '<br/>');
    //filter bodytext to get links
    str += co.data.score 
    + " " + co.data.author 
    + " " + co.data.id + "<br/>" + b;
    if( co.data.replies )
    {
      str += "<ul id='" + co.data.link_id + "'>";
      for( var i = 0; i < co.data.replies.data.children.length; ++i )
      {
        if( co.data.replies.data.children[i].kind == "more" )
        { 
          var tt = "cx" + ++reddit_link_counter;
          str += "<li id='" + tt + "'>" 
          + "<button onclick=\"loadMoreComments(\'" + co.data.link_id + "\', \'" + sub + "\', \'" + co.data.replies.data.children[i].data.children + "\',\'" + tt + "\')\">M</button></li>";
        }
        else
        {
          str += "<li>" + parseCommentTree( co.data.replies.data.children[i], sub ) + "</li>";
        }
      }
      str += "</ul>";
    }
    str += "";
  }//kind == t1
  return str;
}
//render the comments to a string containing DOM.
//obj comes from JSON.parse of a request issued from load_comments.
function render_comments(sub, obj)
{
  var str =  ""; 
  str += linkify( obj[0].data.children[0].data.selftext.replace(/(?:\r\n|\r|\n)/g, '<br/>') );
  str += "<hr/><ul>";
  for( var i = 0; i < obj[1].data.children.length; ++i )
  {
    str += "<li>" + parseCommentTree( obj[1].data.children[i], sub ) + "</li>";
  }
  str += "</ul>";
  return str;
}

//send an XHR to get comments.
function load_comments(sub, c)
{
  var url = "https://www.reddit.com/r/" + sub + "/comments/" + c + "/.json";
  var xh = new XMLHttpRequest();
  xh.onreadystatechange = function()
  {
    console.log( xh.readyState + " " + xh.status );
    if( xh.readyState == 4 )
    {
      if( xh.status == 200 )
      {
        obj = JSON.parse( xh.responseText );
        var s = render_comments(sub, obj);
        cache_set( c, s );
        document.getElementById("ox").innerHTML += s;
      }
    }
  };
  xh.open("GET", url, true);
  xh.send(null);
}

//takes a sub obj and returns a string with the rendered contents.
//the object should come from JSON.parse a request from load_sub.
function render_sub( sub, obj )
{
  var str = "<ul>";
  var first = obj.data.children[0].name;
  var last = "";
  for( var i = 0; i < obj.data.children.length; ++i )
  {
    var t3 = obj.data.children[i];
    last = t3.data.name;
    str += "<li>" + t3.data.ups + "<a id='" + "l" + i + 
            "' href='" + t3.data.url + "' target='_blank'>" +
            t3.data.title + "</a> " + t3.data.domain + " " + 
            t3.data.subreddit + " " + t3.data.author + 
            " <a onclick=\"doLoadThread(\'" + t3.data.subreddit + "\', \'" + t3.data.id + "\' );\" href=#>" + 
            t3.data.num_comments + " comments</a>" +
            + "</li>";
    linkNames[i] = t3.data.name;
    linkThumbnails[i] = t3.data.thumbnail;
    if( t3.data.preview )
    {
      linkPreviews[i] = t3.data.preview.images[0].source.url;
    }
    else
    {
      linkPreviews[i] = "";
    }
  }
  str += "</ul>";
  return str;
}
function load_sub( subName, appendix )
{
  var url = "https://www.reddit.com/r/" + subName + "/.json" + appendix;
  var xh = new XMLHttpRequest();
  xh.onreadystatechange = function()
  {
    if( xh.readyState == 4 && xh.status == 200 )
    {
      var obj = JSON.parse( xh.responseText, null, 4 );
      var str = render_sub( subName, obj );
      cache_set( subName, str );
      document.getElementById("ox").innerHTML = str;
    }
  }
  xh.open("GET", url, true);
  xh.send(null);
}

function doLoadThread( sub, comment_id )
{
  
  //reddit_str_store[ sub ] = document.getElementById("ox").innerHTML;
  document.getElementById("ox").innerHTML = "<button onclick=\"closeComments('" + sub + "')\">Close</button><br/> ";
  if( !cache_check( comment_id ) )
  {
    load_comments( sub, comment_id );
  }
  else
  {
    document.getElementById("ox").innerHTML += cache_get(comment_id);
  }
}

function doLoadSub( sub )
{
  document.getElementById("ox").innerHTML = "";
  if( !cache_check( sub ) )
  {
    load_sub( sub, "" );
  }
  else
  {
    document.getElementById("ox").innerHTML += cache_get( sub );
  }
}

function d()
{
  doLoadThread( "The_Donald", "5s8cck" );
}
function d2()
{
  var s = document.getElementById('subNameBox').value;
  doLoadSub( s );
}

function closeComments( sub )
{
  if( reddit_str_store[ sub ] == null || reddit_str_store[ sub ] == "" )
  {
    doLoadSub( sub );
  }
  else
  {
    document.getElementById("ox").innerHTML = reddit_str_store[ sub ];
  }
}

setTimeout( worker, reddit_worker_delay );

