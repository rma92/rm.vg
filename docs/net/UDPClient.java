import java.io.*;
import java.net.*;
import javax.swing.*;
import java.awt.event.*;
import java.awt.*;

class UDPClient
{
	JFrame f;
	JEditorPane editorPane;
	int s_historyText = 0;
	String historyText = "Welcome. <b>Started.</b><br>";
	int listenPort;
	final String sMyIP;
	final int sMyPort;
	public UDPClient(String IPStr, int port)
	{
		sMyIP = IPStr;
		sMyPort = port;
		f = new JFrame("TesT");
		f.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
		f.setLayout(new BorderLayout());
		editorPane = new JEditorPane("text/html", historyText);
		f.add(new JScrollPane(editorPane), BorderLayout.CENTER);
		this.append("<font color=red>Listening on " + IPStr + ":" + port + "</font><br>");
		JPanel bottomPanel = new JPanel();
		f.add(bottomPanel, BorderLayout.SOUTH);
		Thread t = new Thread()
		{
			public void run()
			{
				try
				{
				DatagramSocket clientSocket = new DatagramSocket(sMyPort); // the port goes here
				InetAddress IPAddress = InetAddress.getByName( sMyIP );
				byte[] receiveData = new byte[1024];
				while(true)
				{
					DatagramPacket receivePacket = new DatagramPacket(receiveData, receiveData.length);
					clientSocket.receive(receivePacket);
					String receiveString = new String(receivePacket.getData());
					receiveString = receiveString.trim();//.substring(0, receiveString.indexOf(0));
					System.out.println("Data from " + receivePacket.getAddress().toString() + ":" + receivePacket.getPort() + " ====\n" + receiveString + "\n===");	
					append("<b>" + receivePacket.getAddress().toString() + ":" + receivePacket.getPort() + "</b>&gt;\'" + receiveString + "\'</br>");
				}//end while
				}catch(Exception e)
				{
					e.printStackTrace();
				}
			}
		};
		t.start();
		f.setSize(600,400);
		f.setVisible(true);
	}

	public void append(String s)
	{
/*
		int sx;
		System.out.println("append");
		while((sx = s_historyText) > 0)
		{
			System.out.println("s_historyText = " + sx);
			try{
				Thread.sleep(10);
			}catch(Exception e){}
		}*/
		System.out.println("...ing");
		++s_historyText;
		historyText += s;
		editorPane.setText(historyText);
		s_historyText--;
	}

	public static void main(String args[]) throws Exception
	{
		new UDPClient("localhost", 5309);
	}//end main
}//end class
		
