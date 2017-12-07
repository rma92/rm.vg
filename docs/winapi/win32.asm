;this is for MASM32 on x86...
;ml /c /Zd /coff win32.asm
;link /SUBSYSTEM:WINDOWS win32.obj
.386 
.model flat, stdcall 
option casemap :none 
include \masm32\include\windows.inc 
include \masm32\include\user32.inc 
include \masm32\include\kernel32.inc 
includelib \masm32\lib\user32.lib 
includelib \masm32\lib\kernel32.lib

WinMain proto :DWORD, :DWORD, :DWORD, :DWORD
.data 
AppName db "TestApp", 0
.data? 
hInstance HINSTANCE ?

.code
start: 
invoke GetModuleHandle, NULL 
mov hInstance, eax 
invoke WinMain, hInstance, NULL, NULL, 0 
invoke ExitProcess, eax 

WinMain proc hInst:HINSTANCE, hPrevInst:HINSTANCE, CmdLine:LPSTR, CmdShow:DWORD 
  local wc:WNDCLASSEX 
  local msg:MSG 
  local hwnd:HWND
  mov wc.cbSize, SIZEOF WNDCLASSEX 
  push hInstance 
  pop wc.hInstance 
  mov wc.lpszClassName, offset AppName
  mov wc.lpfnWndProc, offset WndProc 
  mov wc.style, CS_DBLCLKS or CS_HREDRAW or CS_VREDRAW 
  mov wc.hbrBackground, COLOR_WINDOW+1 
  mov wc.cbClsExtra, NULL
  mov wc.cbWndExtra, NULL
  mov wc.lpszMenuName, NULL
  invoke LoadIcon, NULL, IDI_INFORMATION 
  mov wc.hIcon, eax 
  mov wc.hIconSm, eax 
  invoke LoadCursor, NULL, IDC_ARROW
  mov wc.hCursor, eax
  invoke RegisterClassEx, addr wc
  invoke CreateWindowEx, 0, addr AppName, addr AppName, WS_OVERLAPPEDWINDOW or WS_VISIBLE, CW_USEDEFAULT, CW_USEDEFAULT, 360, 240, NULL, NULL, hInst, NULL
  mov hwnd, eax
  .while TRUE 
    invoke GetMessage, addr msg, NULL, 0, 0 
    .break .if (!eax) 
    invoke TranslateMessage, addr msg 
    invoke DispatchMessage, addr msg 
  .endw
  mov eax, msg.wParam 
  ret 
WinMain endp

WndProc proc hWnd:HWND, uMsg:UINT, wParam:WPARAM, lParam:LPARAM 
  .if uMsg == WM_DESTROY 
    invoke PostQuitMessage, 0 
  .else 
    invoke DefWindowProc, hWnd, uMsg, wParam, lParam 
    ret 
  .endif 
  xor eax, eax 
  ret 
WndProc endp
end start
