#include <stdio.h>
//What is the defined function of 
//*++pointer 
//?
int main()
{
  char * strCX = "ACE";
  printf("%c", *++strCX);
  --strCX;
  printf("%c", ++*strCX);
  printf("%c", *strCX++);
}
