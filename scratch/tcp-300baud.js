var net = require('net');
var fakemtu = 100;
var fakelag = 300;
var kue = [];

//based on https://gonzalo123.com/2012/09/17/building-a-simple-tcp-proxy-server-with-node-js/
//a modification was required so that a connection to the remote server is 
//initialized as soon as a connection to the proxy is, rather than waiting for
//data before starting the connection.  SSH clients do not send data, and the
//server is expected to send data on connection.

var LOCAL_PORT = 1081;
//var REMOTE_PORT = 22; var REMOTE_ADDR = "10.0.0.1";
//var REMOTE_PORT = 80;var REMOTE_ADDR = "129.171.33.6";
var REMOTE_PORT = 4990; var REMOTE_ADDR = "127.0.0.1"; //4990 is a socks proxy.

var server = net.createServer(function (socket)
{
});

//send the data in the queue.
//queue contains objects {socket: (socket), data: (buffer) }

function sender()
{
  if( kue.length > 0 ) console.log("sender - kue length" + kue.length);
  if( kue.length > 0 )
  {
    var o = kue.shift();
    try
    {
      //console.log( o.buffer.toString() );
      o.socket.write( o.buffer );
      setTimeout( function(){ sender()}, fakelag );
    }catch(e)
    {
      console.log( "failed to send kue task:" + e.name + " " + e.message );
      sender();
      //if there is an error sending, go to the next operation.
    }
    return;
  }
  setTimeout( function(){ sender()}, fakelag );
}

function sendChunk( socket, chunks )
{
  if( chunks.length > 0 )
  { 
    console.log( "$" + chunks.length);
    socket.write( chunks.shift());
    sendChunk( socket, chunks );
    //setTimeout( function(){ sendChunk( socket, chunks )}, 500 );
  }
}
server.on('connection', function( socket ){
  console.log('#'); 
  var serviceSocket = new net.Socket();
  serviceSocket.connect(parseInt(REMOTE_PORT), REMOTE_ADDR, function() {
    //console.log('>>FROM proxy 2 remote', "");
    serviceSocket.write("");
  });
    
  serviceSocket.on("data", function (data) {
    //receive remote data
    
    //do something
    
    //socket.write(data);
    //send remote data to client
    //split the data into little pieces.
    console.log( data.length );
    var k = 0;
    var chunks = [];
//    console.log( data + "\n\n\n\n");
    for( var i = 0; i < data.length; i += fakemtu)
    {
      var b2 = Buffer.from( data.slice(i, i+fakemtu) );
//      console.log( b2 );
      var o = {};
      o.socket = socket;
      o.buffer = b2;
      kue.push( o );
      //chunks.push( b2 );
    }
    setTimeout( function(){ sendChunk( socket, chunks )}, 5000 );
  });
  socket.on('data', function( msg )
  {
    //receive client data

    //do something

    //send client data to remote
    try
    {
      serviceSocket.write( msg );
    }
    catch(e)
    {
      console.log( "error writing client data to remote");
    }
  });
});

server.listen( LOCAL_PORT );
console.log('TCP server on port: ' + LOCAL_PORT );
setTimeout( function(){sender() }, fakelag);
