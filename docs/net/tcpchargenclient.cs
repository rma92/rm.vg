using System;
using System.Net;
using System.Net.Sockets;
using System.Text;

class Hello 
{
  static void Main() 
  {
    Socket sending_socket = new Socket(AddressFamily.InterNetwork, SocketType.Stream, ProtocolType.Tcp);
    IPAddress toAddress = IPAddress.Parse("127.0.0.1");
    IPEndPoint endPoint = new IPEndPoint(toAddress,19);
    sending_socket.Connect(endPoint);

    string receivedData;
    byte[] receivedByteArray = new byte[1536];
    byte[] sendBuffer = Encoding.ASCII.GetBytes(" ");
    //Console.WriteLine("sending to address: {0} port: {1}", endPoint.Address, endPoint.Port );
    try
    {
      //sending_socket.SendTo(sendBuffer, endPoint);
      while(true)
      {
        int i = sending_socket.Receive( receivedByteArray);      
        receivedData = Encoding.ASCII.GetString(receivedByteArray, 0, receivedByteArray.Length);
        receivedData = receivedData.Substring(0,i);
        Console.Write("{0}", receivedData);
      }
    }
    catch (Exception e)
    {
      Console.WriteLine(" Exception {0}", e.Message);
    }
  }//end of main()
}

