#define WIN32_LEAN_AND_MEAN
#include <stdio.h>
#include <time.h>
#include <malloc.h>
#include <winsock2.h>
#include <ws2tcpip.h>
#include <windows.h>

//GCC Win32: gcc mclisten.c -l ws2_32
//MS VCC:    cl mclisten.c /link WS2_32.LIB
int main(int argc, char ** argv)
{
  SOCKET sock;
  WSADATA WinsockData;
  int listen_port = 5555;
  int err;
  char* recv_buf = calloc(1, 32768);
  int sz_recv_buf = 32768;
  char* listen_if_ip;
  char* mc_domain_ip;
  //handle parameters
  if(argc < 3)
  {
    printf("usage: mclisten ip_inf mc_domain [listen_port]\nex: mclisten 0.0.0.0 239.1.2.3\nlisten on multicast channel 239.1.2.3 on any ip interface.\nDefault port: 5555");
    return 0;
  }

  listen_if_ip = argv[1];
  mc_domain_ip = argv[2];
  if(argc > 3)
  {
    listen_port = atoi(argv[3]);
  }

  //startup WinSock
  if(WSAStartup(MAKEWORD(2, 2), &WinsockData) != 0)
  {
    printf("wrong winsock version.");
    return 2;
  }

  //Create a socket.
  sock = WSASocket(AF_INET, SOCK_DGRAM, IPPROTO_UDP, 0, 0, 0);
  if(sock == SOCKET_ERROR)
  {
    printf("failed to get socket:%d\n", WSAGetLastError()); return 1;
  }

  printf("making socket.\n");
  //Create the multicast request.
  struct ip_mreq mreqv4 = {0};
  struct sockaddr_in service_in;
  //InetPton(AF_INET, "255.255.255.255", mreqv4.imr_multiaddr);
  mreqv4.imr_multiaddr.S_un.S_addr = inet_addr( mc_domain_ip );
  mreqv4.imr_interface.S_un.S_addr = inet_addr( listen_if_ip );
  service_in.sin_family = AF_INET;
  service_in.sin_addr.s_addr = mreqv4.imr_interface.S_un.S_addr;
  service_in.sin_port = htons(listen_port);
  //bind to the local port.
  err = bind(sock, (SOCKADDR*) &service_in, sizeof(service_in));
  if(err == SOCKET_ERROR)
  {
    printf("Error with bind(): %d\n",
      WSAGetLastError());
    return 1;
  }
  else
  {
    printf("bind okay on %s:%d\n",
      inet_ntoa(mreqv4.imr_interface),
      listen_port);
  }
  //add the v4 socket to the v4 group.
  err = setsockopt(sock, IPPROTO_IP, IP_ADD_MEMBERSHIP,
    (char*)&mreqv4, sizeof(mreqv4));
  if(err == SOCKET_ERROR)
  {
    printf("error adding membership: %d.\n",
      WSAGetLastError());
    return 1;
  }
  //set the interface for outgoing traffic to this mc address
  err = setsockopt(sock, IPPROTO_IP, IP_MULTICAST_IF,
  (char*) &mreqv4.imr_interface.S_un.S_addr, 
  sizeof(mreqv4.imr_interface.S_un.S_addr));
  if(err == SOCKET_ERROR)
  {
    printf("error setting the outgoing address: %d.\n",
      WSAGetLastError());
    return 1;
  }

  struct sockaddr_in recv_addr;
  //recv_addr.sin_family = AF_INET;
  //recv_addr.sin_port = htons(listen_port);
  int sz_recv_addr = sizeof(recv_addr);
  
  printf("listening until Ctrl+C\n");
  while(1)
  {
    //recv(sock, recv_buf, sz_recv_buf, 0);
    int err = recvfrom(sock, recv_buf, sz_recv_buf, 0, (SOCKADDR*) &recv_addr, &sz_recv_addr);
    if(err == SOCKET_ERROR)
    {
      printf("error: %d", WSAGetLastError());
      break;
    }
    printf("Recv: %s\n", recv_buf);
    memset(recv_buf, 0, sz_recv_buf);
  }
  closesocket(sock);
  WSACleanup();
}

