#include <stdio.h>
/*
Notes on inline ASM
syntax:
__asm__( code : outs : ins : clobber registers )
= write-only
+ read-only
to output a register to a C variable named z:
"=r" (z) 
to input a C variable named a to a register
"=r" (a)
the differeint inputs or outputs can be split with commas:
"=r" (a), "=r" (b)
the type constraints are 
g - general effective address
m - memory effective address
r - register
i - immediate value, 0..0xffffffff
n - immediate value known at compile time
x86 specific ones are
q - byte-addressable register (eax, ebx, ecx, edx)
A - eax or edx
a, b, c, d, S, D - eax, ebx, ecx, edx, esi, edi respectively
I - immediate 0..31
J - immediate 0..63
K - immediate 255
L - immediate 65535
M - immediate 0..3 (shifts that can be done with lea)
N - immediate 0..255 (one-byte immediate value)
O - immediate 0..32
Clobbers is a list of registers that are clobbered by the
operation, a comma separated list. 2 special cases.
"memory" is if something (other than an output) is written to memory
"cc" is if something messes up condition codes. May not be needed
      on the x86, but always legal to specify.
If an input register is clobbered, include a dummy output in that 
  register which is simply never used (GCC >2.8 is picky)
const and volatile
asm volatile(...) means that no reordering should occur
  (prevents deletion if outputs unused)
asm const(...) assumed to produce outputs varying only based on
  inputs. DO NOT USE FOR POINTERS as they may change.

*/

int main()
{
  int x = 3;
  int y = 2;
  int z = 0;
  printf("x=%d y=%d z=%d\n", x, y, z);
  //add something
  __asm__(
   ".intel_syntax noprefix; \n"\
   "mov eax, %1 \n"\
   "mov ebx, %2 \n"\
   "add eax, ebx \n"\
   "mov %0, eax \n"\
   ".att_syntax \n"
   : "+r" (z)
   : "r"(x), "r"(y)
   : 
   );
  printf("x=%d y=%d z=%d\n", x, y, z);
  //swap x and y
  z = 0;
  __asm__(
   ".intel_syntax noprefix; \n"\
   "xchg %1, %2\n"\
   ".att_syntax \n"
   : "+r" (z), "+r"(x), "+r"(y)
   );
  printf("x=%d y=%d z=%d\n", x, y, z);

  //make a mess (put the sum in all of the variables)
  z = 0;
  __asm__(
   ".intel_syntax noprefix; \n"\
   "mov eax, %1 \n"\
   "mov ebx, %2 \n"\
   "add eax, ebx \n"\
   "mov %0, eax \n"\
   "mov %1, eax \n"\
   "mov %2, eax \n"\
   ".att_syntax \n"
   : "+r" (z), "+r"(x), "+r"(y)
   );
  printf("x=%d y=%d z=%d\n", x, y, z);
}
