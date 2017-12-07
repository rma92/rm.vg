#based on https://gist.github.com/javierg/0ae30e7ea47d3d18c39a

defmodule SimpleTcp do
  def start_server(port) do
    pid = spawn fn ->
      {:ok, listen} = :gen_tcp.listen(port, [:binary, {:active, false}])
      spawn fn -> acceptor(listen) end
      :timer.sleep(:infinity)
    end
    {:ok, pid}
  end

  def acceptor(listen_socket) do
    {:ok, socket} = :gen_tcp.accept(listen_socket)
    spawn fn -> acceptor(listen_socket) end
    handle(socket)
  end

  def httpsendmessage(socket, httpmessage, content)

  end

  def handle(socket) do
    :inet.setopts(socket, [{:active, :once}])
    receive do
      {:tcp, socket , "quit" <> _} ->
        :gen_tcp.close(socket)
      {:tcp, socket, msg} ->
        pattern = :binary.compile_pattern(["\r\n", "\n"])
        msglines = String.split(msg, pattern)
        httpcmd = String.split( hd(msglines), " ")
        if length(httpcmd) < 3 do
          IO.puts("invalid http cmd")
          IO.inspect( httpcmd )
          :gen_tcp.send(socket, "Invalid CMD" <> msg);
        else
          IO.puts("check http cmd")
          IO.inspect( httpcmd )
          httpverb = hd(httpcmd)
          httpfile = tl(httpcmd)
          httpver = tl(httpfile)
          httpfile = hd(httpcmd)
          if httpverb = "GET" do
            content = "<html><head><title>404 Not Found</title></head>\n<body><h1>404 Not Found</h1></body></html>"
            :gen_tcp.send( socket, "HTTP/1.1 404 Not Found\r\nServer: trump/0.1\r\nContent-Length:" 
              <> Integer.to_string( String.length(content) )
              <> "\r\nConnection:close\r\nContent-Type: text/html\r\n\r\n" <> content )
          end #httpverb = "GET"
        end
        if hd(msglines) == "b" do
          :gen_tcp.send(socket, "bob")
        end
        handle(socket)
    end
  end
end

SimpleTcp.start_server(1200)

