#based on https://gist.github.com/javierg/0ae30e7ea47d3d18c39a

#issues: ignores mime types
#warning: Security issues -- directory handling is insecure
#warning: directory listing is vulnerable to XSS attacks.
defmodule SimpleTcp do
  def getConf( conf_name ) do
    Agent.get(:conf_store, fn map-> Map.get( map, conf_name) end)
  end
  
  def setConf( conf_name, new_value) do
    Agent.update(:conf_store, fn map-> Map.put( map, conf_name, new_value) end )
  end

  def start_server(port) do
    #Set up the server
    #Set up an agent to hold a KV pair of config data.
    {:ok, apid} = Agent.start_link( fn -> %{} end )
    Process.register(apid, :conf_store)
    setConf( "base_dir", System.cwd())

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

  def http_respond(socket, httpmessage, content) do
    :gen_tcp.send( socket, "HTTP/1.1 httpmessage\r\nServer: trump/0.1\r\nContent-Length:" 
      <> Integer.to_string( byte_size(content) )
      <> "\r\nConnection:close\r\nContent-Type: text/html\r\n\r\n" <> content )
  end

  #send the correct content based on error code.
  #404, xtra should be filename
  #500
  def http_send_page(socket, http_code, xtra) do
    if http_code == 404 do
      http_respond( socket, "404 Not Found", 
        "<html><body>File '" <> xtra <> "' not found.</body></html>" )
    end
  end

  #for http_get_file_handle
  #concat list of strings onto string
  def concat_dir(string, list) do
    string = string <> "<a href='" <> hd(list) <> "'>" <> hd(list) <> "</a><br/>"

    IO.inspect( list )
    if ( length( list ) > 1 ) do
      concat_dir(string, tl(list) )
    else
      IO.inspect( string )
    end
  end

  #handle a normal http get for a file
  #dead code if the server is used to provide a front end to an application
  def http_get_file_handle(socket, filename) do    
    base_dir = getConf( "base_dir")
    fpath = base_dir <> filename
    if( File.exists?( fpath ) ) do
      if( File.dir?( fpath ) ) do
        #see if there is index.html
        if( File.exists?( fpath <> "/index.html" ) ) do
          case File.read( fpath <> "/index.html" ) do
            {:ok, body } -> http_respond(socket, "200 OK", body )
            {:error, reason } -> 
              http_send_page( socket, 404, filename)
          end
        else
          files = elem( File.ls( fpath ), 1)
          content = "<html><body><h1>dir list of '" 
                      <> filename 
                      <> "'</h1><br/>"
          content = concat_dir( content, files )
          content = content <> "</body></html>"
          http_respond(socket, "200 OK", content)
        end
      else
        case File.read( fpath ) do
          {:ok, body } -> http_respond(socket, "200 OK", body )
          {:error, reason } -> 
              http_send_page( socket, 404, filename)
        end
      end  
    else
      http_send_page( socket, 404, filename)
    end

  end #http_get_file_handle

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
          httpver = hd( tl(httpfile) )
          httpfile = hd(httpfile)
          IO.puts( "verb = " <> httpverb)
          IO.puts(" file = " <> httpfile)
          IO.puts(" ver = " <> httpver )
          if httpverb == "GET" do
            http_get_file_handle( socket, httpfile )
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

