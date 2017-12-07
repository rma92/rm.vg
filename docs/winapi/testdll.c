#include <windows.h>
//Sample DLL in C 
//Tiny C Compiler: tcc testdll.c -shared -o testdll.tcc.dll
//MS VC++: cl /D_USRDLL /D_WINDLL testdll.c user32.lib /MT /link /DLL /OUT:testdll.vc.dll

//rundll32 tccdll.dll,my_function some text
//(displays a message box containing "some text")

/*
For C++, wrap the functions in extern "C" {} to avoid mangling the 
function names.

Possibly add a :: in front of MessageBox.
*/

//Note: using Rundll32 to call by ordinal number results in parameters
//  not being passed correctly.
__declspec (dllexport) 
int __cdecl my_function ( HWND hwnd, HINSTANCE hinst,
  LPTSTR lpCmdLine, int nCmdShow ) 
  {
    MessageBox(0,lpCmdLine,0,0);
    return 0;
  }

BOOL APIENTRY DllMain( HANDLE hModule, 
  DWORD  ul_reason_for_call, 
  LPVOID lpReserved)
  {
    return TRUE;
  }

