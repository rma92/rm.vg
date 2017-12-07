using System;
using System.Net;
using System.Net.Sockets;
using System.Text;
using System.Threading;

//A Basic Threaded Echo Server. to use, create RMEchoServer( 7000 ); or whatever port you want.
public class RMEchoServer
{
  private TcpListener myListener;
  private Thread listenThread;
  private int port;

  public RMEchoServer(int myPort)
  {
    port = myPort;
    try
    {
      myListener = new TcpListener(IPAddress.Any, port);
      
      listenThread = new Thread(new ThreadStart(ClientListener));
      listenThread.Start();
      
    }
    catch(Exception ex)
    {
      Console.WriteLine("Exception on start listen: " + ex.ToString());
    }
  }

  public void ClientListener()
  {
    myListener.Start();
    Console.WriteLine("Running on *:" + port + "...\n");
    while(true)
    {
      TcpClient client = myListener.AcceptTcpClient();
      Thread clientThread = new Thread( new ParameterizedThreadStart( HandleClientComm));
      clientThread.Start(client);
    }
  }

  public void HandleClientComm( object client )
  {
    TcpClient tcpClient = (TcpClient)client;
    NetworkStream clientStream = tcpClient.GetStream();
    int maxReadLength = 4096;

    byte[] message = new byte[maxReadLength];
    int idx = 0;
    int len = 72;
    string sx = "!\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~ !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~ !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~ ";
    Console.WriteLine("Connected!");
      ASCIIEncoding enc = new ASCIIEncoding();
    
    while( true )
    {
    /*
      bytesRead = 0;
      try
      {
        bytesRead = clientStream.Read(message, 0, maxReadLength);
        Console.WriteLine("Read");
      }
      catch
      {
        break;
      }

      if (bytesRead == 0)
      {
        break;
      }

      //Print out the request
      Console.WriteLine( ":" + enc.GetString( message, 0, bytesRead ) + ":");

      //Send back a response.
      //String output = "Not used";
      byte[] buffer = message; //Echo sends back whatever it got.
      //enc.GetBytes(output);
      */
      if(++idx > 72){ idx = 0; } 
      
      byte[] buffer = enc.GetBytes( sx.Substring(idx, idx+len) + "\n" );
      try
      {
        clientStream.Write(buffer, 0, buffer.Length);
        clientStream.Flush();
      }
      catch(Exception ex)
      {
        break;
      }
    }
    
    Console.WriteLine("Disconnecting.");
    tcpClient.Close();
  }
}

public class UDPListen
{
  public static void Main()
  {
    new RMEchoServer(19);
    Console.ReadLine();
  }
}

