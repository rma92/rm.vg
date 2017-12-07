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
 
  console.log( newFileArr );
  dnsFileStorageMap.set( name, newFileArr );
}

function buildDNSCache()
{
  console.log("build");
  
}

function buildDNSCacheWrapper()
{
  //check if we need to build DNS file cache.
  if( true )
  {
    buildDNSCache();
  }
}

dnsd.createServer( function(req,res){
  var question = res.question[0]
    , hostname = question.name
    , length = hostname.length
    , ttl = Math.floor(Math.random() * 3600)

  if( question.type == 'A')
  {
    res.end('1.2.3.4');
  }
  else
  {
    var questionSplit = question.name.split(".");

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
