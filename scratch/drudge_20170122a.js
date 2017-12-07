var http = require('http');
var cheerio = require('cheerio');
var url = require('url');
var dnsd = require('dnsd');
var crypto = require('crypto');//used for hash generation.
var options = {
  host: 'drudgereport.com',
  port: '80',
  path: '/'
};

var linkText = [];
var linkUrl = [];
var pages = [];
var output = "";
var callback = function(response){
  var body = '';
  response.on('data', function(data)
  {
    body += data;
  });
  response.on('end', function(){
    //var parsedHTML = cheerio.load(body);
    var p2 = body.split("<!");
    for(var i = 0; i < p2.length; ++i )
    {
      //console.log(p2[i]);
      var p2bracketIndex = p2[i].indexOf(">");
      var p2a = p2[i].substring(0, p2bracketIndex);
      if( p2a[0] == ' ' && p2a.trim()[0] != 'L')
      {
        pages.push([]);
        console.log("$$$$$" + p2a.trim() + "$$$$$");
        var parsedHTML = cheerio.load(p2[i]);
        parsedHTML('a').map( function(i, link) {
          var href= cheerio(link).attr('href');
          var text = cheerio(link).text();
          pages[pages.length-1].push( linkText.length);
//          (pages[i]).push( linkText.length);
          linkText.push(text);
          linkUrl.push(href);
        });
      }
    }
    /*
    for(var i = 0; i < linkText.length; ++i )
    {
      output += linkText[i] + "\n";
    }
    console.log(output)
    */
  });//on end function
}

var req = http.request(options, callback);
req.end();

http.createServer( 
  function( request, response){
    var pathname = url.parse(request.url).pathname;
    console.log("Request for " + pathname + " received.");
    if( pathname == "/")
    {
      response.writeHead(200, {'Content-Type': 'text/html'});
      response.write("<html><body><h1>DrudgeReport</h1>");
      /*for(var i = 0; i < pages.length; ++i )
      {
        for( var j = 0; j < pages[i].length; ++j )
        {
          response.write( linkText[pages[i][j]] );
        }
        response.write("<br/>");
      }*/
      for( var j = 0; j < pages[0].length; ++j )
      {
        response.write( "<a href='" 
          + linkUrl[pages[0][j]] 
          + "'>" + linkText[pages[0][j]] + "</a><br/>");
      }
      for( var j = 0; j < pages[1].length; ++j )
      {
        response.write( "<a href='" 
          + linkUrl[pages[1][j]] 
          + "'>" + linkText[pages[1][j]] + "</a><br/>");
      }
      response.write("<a href=/1>1</a><br/><a href=/2>2</a><br/><a href=/3>3</a><br/>");
      response.write("</body></html>");
    }
    else if( pathname == "/1")
    {
      response.write("<html><body><h1>DrudgeReport1</h1>");
      for( var j = 0; j < pages[2].length; ++j )
      {
        response.write( "<a href='" 
          + linkUrl[pages[2][j]] 
          + "'>" + linkText[pages[2][j]] + "</a><br/>");
      }
      response.write("</body></html>");
    }
    else if( pathname == "/2")
    {
      response.write("<html><body><h1>DrudgeReport2</h1>");
      for( var j = 0; j < pages[3].length; ++j )
      {
        response.write( "<a href='" 
          + linkUrl[pages[3][j]] 
          + "'>" + linkText[pages[3][j]] + "</a><br/>");
      }
      response.write("</body></html>");
    }
    else if( pathname == "/3")
    {
      response.write("<html><body><h1>DrudgeReport1</h1>");
      for( var j = 0; j < pages[4].length; ++j )
      {
        response.write( "<a href='" 
          + linkUrl[pages[4][j]] 
          + "'>" + linkText[pages[4][j]] + "</a><br/>");
      }
      response.write("</body></html>");
    }
    else
    {
    response.writeHead(404, {'Content-Type': 'text/html'});
    }
    response.end();
}).listen(8080);
var dnsFileStorageMap = new Map();
var dnsStorageMapUpdateTime = 0;
/*
  Creates an object that will contain the split file and it's metadata
  and inserts that into the map.

  File Container Object:
  hash = a hash of the object.
  createTime = the Unix timestamp the object was inserted.
  c = an array of 255 byte parts.
*/
function insertDataIntoMap(name, contents)
{
  //console.log("starting instert for '" + name + "'");
  if( dnsFileStorageMap.has( name ) )
  {
    //get rid of the item that exists if needed.
    console.log("overwriting '" + name + "'");
  }
  var newFileArr = {};
  newFileArr.hash = crypto.createHash('md5').update(contents).digest("hex");
  newFileArr.createTime = Math.floor(new Date() / 1000);
  newFileArr.c = []; //contents
  for( var i = 0, k = -1; i < contents.length; ++i )
  {
    if(i % 250 == 0 )
    {
      ++k;
      newFileArr.c[k] = "";
    }
    newFileArr.c[k] += contents[i];
  }
 
  //console.log( newFileArr );
  dnsFileStorageMap.set( name, newFileArr );
}

function buildDNSCache()
{
  //console.log("building dns cache.");
  //build d0 (headlines)
  var d0str = "";
  for( var j = 0; j < pages[0].length; ++j )
  {
    d0str += linkText[pages[0][j]] + "," + pages[0][j] + "\n";
  }
  for( var j = 0; j < pages[1].length; ++j )
  {
    d0str += linkText[pages[1][j]] + "," + pages[1][j] + "\n";
  }
  //build d1 (left)
  var d1str = "";
  for( var j = 0; j < pages[2].length; ++j )
  {
    d1str += linkText[pages[2][j]] + "," + pages[2][j] + "\n";
  }
  //build d2 (center)
  var d2str = "";
  for( var j = 0; j < pages[3].length; ++j )
  {
    d2str += linkText[pages[3][j]] + "," + pages[3][j] + "\n";
  }
  //build d3 (right)
  var d3str = "";
  for( var j = 0; j < pages[4].length; ++j )
  {
    d3str += linkText[pages[4][j]] + "," + pages[4][j] + "\n";
  }
  //console.log("string construction completed...");
  insertDataIntoMap("d0", d0str);
  insertDataIntoMap("d1", d1str);
  insertDataIntoMap("d2", d2str);
  insertDataIntoMap("d3", d3str);
  //add all links.
  for( var j = 0; j < linkUrl.length; ++j )
  {
    insertDataIntoMap( "l" + j, linkUrl[j] );
  }
  console.log("build finished.");
  //update the dns storage time.
  dnsStorageMapUpdateTime = Math.floor(new Date() / 1000)
}

function buildDNSCacheWrapper()
{
  //check if we need to build DNS file cache.
  console.log("starting build check");
  var now = Math.floor(new Date() / 1000);
  //if there is more than 60 seconds since we last rebuilt the cache
  //build it again.
  if( now > dnsStorageMapUpdateTime + 60 )
  {
    buildDNSCache();
  }
}
/*
  Create a DNS Server and respond in the following manner:
  [query].[filename].(remaining sections ignored)
  where query may be
  q                 Return a TXT record with the number of parts.
  h                 Return a TXT record with the md5 hash of all parts 
                      concatinated.
  an integer        Return a TXT record with the part corresponding to
                      the number specified.  This is a 0-based index.
                      (if there is only one part, look up 0.filename.)

  For purposes of Drudge Report, the following files exist:
  d0      Headlines
  d1      Left column
  d2      Center column
  d3      Right column
  l0-l##  The URL of the links provided.
  
  d0-d3 return CSVs, where the first part is the link text and the
  second part is the link id.  To get the URL, request 0.l#.(ignored)
  where the number is the number of the link to click.  Note that the
  link numbers may change if the cache is rebuilt.  This is to be
  addressed later.
*/
dnsd.createServer( function(req,res){
  var question = res.question[0]
    , hostname = question.name
    , length = hostname.length
    , ttl = Math.floor(Math.random() * 60) /*very short ttl*/

  if( question.type == 'A')
  {
    res.end('1.2.3.4');
  }
  else
  {
    var questionSplit = question.name.split(".");
    buildDNSCacheWrapper();
    var retData = "";
    if( questionSplit[1] != null && questionSplit[0] != null )
    {//sanity check.
      //TODO: build the DNS cache.
      //questionSplit[1] shall be the name of the object.
      //questionSplit[0] shall be the index of the part, or h (hash) or
      //q (number of parts query)
      if( dnsFileStorageMap.has( questionSplit[1] ) )
      {
        fileArr = dnsFileStorageMap.get( questionSplit[1] );
        if( questionSplit[0].toLowerCase() == 'q' )
        {
          var qs = fileArr.c.length.toString();
          res.answer.push({name:hostname, type:'TXT',data: qs,'ttl':ttl});
        }
        else if( questionSplit[0].toLowerCase() == 'h' )
        {
          var qs = fileArr.hash;
          console.log( "qs:" + qs);
          res.answer.push({name:hostname, type:'TXT',data: qs,'ttl':ttl});
        }
        if( !isNaN(questionSplit[0]) && Number.isInteger(parseInt( questionSplit[0] )) )
        {
          var number = parseInt( questionSplit[0]);
          var qs = "";
          if( fileArr.c[ number ] != null) 
          {
            qs = fileArr.c[ number ];
          }
          res.answer.push({name:hostname, type:'TXT',authoritative: true, data: qs,'ttl':ttl});
        }
      }
    }//sanity check. Do nothing if it fails.
    console.log("that's a wrap.");
    res.end();
  }
}).listen(53, '127.0.0.1');

console.log("Server http://127.0.0.1:8080");
console.log("DNS Server 127.0.0.1:53");

insertDataIntoMap("d0", "This is a test, it is only a test.");

setTimeout( buildDNSCache, 2000);
