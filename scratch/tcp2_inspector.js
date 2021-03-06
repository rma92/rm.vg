//http://stackoverflow.com/questions/6490898/node-js-forward-all-traffic-from-port-a-to-port-b
var net = require('net');

// parse "80" and "localhost:80" or even "42mEANINg-life.com:80"
var addrRegex = /^(([a-zA-Z\-\.0-9]+):)?(\d+)$/;

var addr = {
    from: addrRegex.exec(process.argv[2]),
    to: addrRegex.exec(process.argv[3])
};

if (!addr.from || !addr.to) {
    console.log('Usage: <from> <to>');
    return;
}

net.createServer(function(from) {
    var to = net.createConnection({
        host: addr.to[2],
        port: addr.to[3]
    });
    
    from.on('connect', function(msg){
      console.log("#FROMO===============\n" + msg + "\n");
    });
    to.on('connect', function(msg){
      console.log("#TO===============\n" + msg + "\n");
    });
    to.on('data', function(msg){
      console.log("$TO===============\n" + msg + "\n");
    });
    from.on('data', function(msg){
      console.log("$FROM-------------\n" + msg + "\n");
    });
    from.on('end',  function(msg){
      console.log("end from");
    });
    from.pipe(to);
    to.pipe(from);
}).listen(addr.from[3], addr.from[2]);

