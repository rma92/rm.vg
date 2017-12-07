var net = require('net');
require('longjohn');
var fakemtu = 1492;
var fakelag = 21;
var kueUp = [];
var kueDown = [];
process.on('uncaughtException', function (err) {
  console.error(err.stack);
  console.log("Node NOT Exiting...");
});

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

function senderDown()
{
  if( kueDown.length > 0 ) console.log("sender - kueDown length" + kueDown.length);
  if( kueDown.length > 0 )
  {
    var o = kueDown.shift();
    try
    {
      //console.log( o.buffer.toString() );
      console.log("sending...");
      o.socket.write( o.buffer );
      console.log("successfully sent");
      setTimeout( function(){ senderDown()}, fakelag );
      console.log("reschedule complete.");
    }catch(e)
    {
      console.log( "failed to send kueDown task:" + e.name + " " + e.message );
      senderDown();
      //if there is an error sending, go to the next operation.
    }
    return;
  }
  setTimeout( function(){ senderDown()}, fakelag );
}
function senderUp()
{
  if( kueUp.length > 0 ) console.log("sender - kueDown length" + kueUp.length);
  if( kueUp.length > 0 )
  {
    var o = kueUp.shift();
    try
    {
      //console.log( o.buffer.toString() );
      console.log("sending...");
      o.socket.write( o.buffer );
      console.log("successfully sent");
      setTimeout( function(){ senderUp()}, fakelag );
      console.log("reschedule complete.");
    }catch(e)
    {
      console.log( "failed to send kueUp task:" + e.name + " " + e.message );
      senderUp();
      //if there is an error sending, go to the next operation.
    }
    return;
  }
  setTimeout( function(){ senderUp()}, fakelag );
}

server.on('connection', function( socket ){
  console.log('#'); 
  var serviceSocket = new net.Socket();
  serviceSocket.connect(parseInt(REMOTE_PORT), REMOTE_ADDR, function() {
    //console.log('>>FROM proxy 2 remote', "");
    try{
      console.log("new conncetion writing to remote service socket.");
      serviceSocket.write("");
      console.log("done writing to remote socket.");
    }
    catch(e)
    {
      console.log("While writing service socket: " + e.Message );
    }
  });
    
  serviceSocket.on("data", function (data) {
    //receive remote data
    
    //do something
    
    //send remote data to client
    //split the data into little pieces.
    console.log( "parsing incoming data: " + data.length );
    for( var i = 0; i < data.length; i += fakemtu)
    {
      var b2 = Buffer.from( data.slice(i, i+fakemtu) );
      var o = {};
      o.socket = socket;
      o.buffer = b2;
      kueDown.push( o );
    }
    console.log("done parsing");
  });

  socket.on('data', function( msg )
  {
    console.log(" getting client data.");
    //receive client data

    //do something

    //send client data to remote
      console.log("writing service socket");
      console.log("paring client data: " + msg.length );
      for( var i = 0; i < msg.length; i += fakemtu)
    {
      var b2 = Buffer.from( msg.slice(i, i+fakemtu) );
      var o = {};
      o.socket = serviceSocket;
      o.buffer = b2;
      kueUp.push( o );
    }
//      serviceSocket.write( msg );
      console.log("Done writing service socket.");
  });
});

server.listen( LOCAL_PORT );
console.log('TCP server on port: ' + LOCAL_PORT );
setTimeout( function(){senderDown() }, fakelag);
setTimeout( function(){senderUp() }, fakelag);
