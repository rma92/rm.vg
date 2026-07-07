/*
Mode of operation ( arbitrary-data are additional ordinals which are ignored,
e.g. gid.(randomdata).test.google.com)

DNS client requests to write will be: g.[connectionid].(base64-data).arbitrary-data
Except for a DNS request to create a client id: gid.(randomdata).arbitrary-data.  A new connection structure will be created on the server, and it will open a connection to the target server.  It will then return the connection id as a base64 encoded string CNAME.

DNS client request to read will be: r.[connectionid].(arbitrary-data)
The response will be in a cname.

Variables: connections -- server side array of active connection objects.
    payloadsize -- the maximum size of each payload. (def 32)

Data structures:
connection class - stored on the server, represents a TCP connection.
{
  socket - the actual TCP socket.
  upload - array of buffers (each buffer is upto payloadsize) of data 
           from the client that needs to be _written to socket_.
  download - array of buffers (each buffer is upto payloadsizes) of data
           from the socket that the client needs to get.
           When a client requests information, it is obtained using
            download.shift.
}
*/

var dns = require('native-dns');
var net = require('net');
process.on('uncaughtException', function (err) {
  console.error(err.stack);
  console.log("Node NOT Exiting...");
});

var REMOTE_PORT = 80; var REMOTE_ADDR = "129.171.33.6";
var REMOTE_PORT = 22; var REMOTE_ADDR = "8.8.8.8";//SSH works well

server = dns.createServer();

//connection object store on the server.
var connections = [];
var payloadsize = 32; //size in the number of bytes of each buffer to encode.

function heartbeat()
{
//deal with anything that needs dealing with.
//console.log("heartbeat");
for( var i = 0; i < connections.length; ++i )
{
  //console.log( "i[" + i + "] upload length [" + connections[i].upload.length + "]");
  while( connections[i].upload.length > 0 )
  {
    //console.log('sending...');
    var b = connections[i].upload.shift();
    connections[i].socket.write( b );
  }
}

setTimeout( heartbeat , 100);
}

//get conncection connid, and append the contents of buffer to it's 
//upload buffer.
function appendConnUpload(connid, buffer)
{
  console.log("appending [" + connid + "]");
  if( connections[connid] != null && connections[connid].upload != null )
  {
    console.log("found");
    connections[connid].upload.push( buffer );
  }
  else
  {
    console.log("conn is null");
  }
}

//create a connection object, start the connection, 
//if that works, put it in connections[] and return the connection id.
function initConn()
{
  console.log('creating new connnection...');
  var o = {}; //new object
  o.socket = new net.Socket();
  o.socket.connect( parseInt( REMOTE_PORT ), REMOTE_ADDR,
    function(){
      try
      { 
        console.log("writing new socket");
        o.socket.write("");
      }catch(e)
      {
        console.log("While writing new socket: " + e.Message);
      }
    });
  o.upload = [];
  o.download = [];
  
  o.socket.on("data", function(data)
  {
    //split the data from the server.
    for( var i = 0; i < data.length; i += payloadsize )
    {
      var b2 = Buffer.from( data.slice( i, i+payloadsize ) );
      console.log("data" + i + ":" + b2.toString());
      o.download.push( b2 );
    }
  });//o.socket.on data
  var l = connections.length;
  connections[l] = o;
  console.log('id: ' + l);
  return l;
}

server.on('request', function( request, response )
{
  var ords = request.question[0].name.split('.');
  console.log( request.question[0] + " " + ords ); 

  response.header.aa = 1;  
  if( ords[0] == 'gid' )
  {//if there is a request to start a connection.
    var newConnectionId = initConn();
    response.answer.push( dns.CNAME(
    {
      name: request.question[0].name,
      ttl: 10,
      data: Buffer.from( newConnectionId.toString() ).toString('base64')
    }
    ));
  }
  else if( ords[0] == 'r' ) //read data
  {
    //console.log("request to read");
    if( !isNaN ( ords[1] ) )
    {
      var connid = parseInt( ords[1] );
      //console.log("read conn " + connid);
      if( connections[connid] != null 
          && connections[connid].download.length > 0 )
      {
        var b2 = connections[connid].download.shift();
        //console.log("conn " + connid + ":" + b2.toString());
        response.answer.push( dns.CNAME(
        {
          name: request.question[0].name,
          ttl: 10,
          data: b2.toString('base64')
        }
        ));
      }
      else
      {
        //no data.
        //console.log("nothing to read");
      }
    }
  }
  else if( ords[0] == 'w' )
  {
    console.log("request to write");
    if( !isNaN ( ords[1] ) )
    {
      var connid = parseInt( ords[1] );
      var b2 = Buffer.from( ords[2], 'base64');
      console.log("adding to upload for conn " + connid 
        + ": '" + b2 + "'");
      appendConnUpload( connid, b2 );
    }
    else
    {

    }
  }
  else
  {//do whatever.
    response.answer.push( dns.A(
    {
      name: request.question[0].name,
      address: '8.8.8.8',
      ttl: 10
    }));
    response.answer.push( dns.CNAME(
    {
      name: request.question[0].name,
      ttl: 10,
      data: Buffer.from( "I like trains" ).toString('base64')
    }
    ));
  }//else
  response.send();
});

server.serve(53);
heartbeat();
