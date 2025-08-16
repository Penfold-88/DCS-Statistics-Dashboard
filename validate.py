#!/usr/bin/env python3
import re

def check_powershell_syntax(filename):
    with open(filename, 'r', encoding='utf-8') as f:
        lines = f.readlines()
    
    brace_stack = []
    paren_stack = []
    in_string = False
    string_char = None
    
    for line_num, line in enumerate(lines, 1):
        i = 0
        while i < len(line):
            char = line[i]
            
            # Handle strings
            if char in ['"', "'"] and (i == 0 or line[i-1] != '`'):
                if not in_string:
                    in_string = True
                    string_char = char
                elif char == string_char:
                    in_string = False
                    string_char = None
            
            # Skip string contents
            if in_string:
                i += 1
                continue
            
            # Track braces
            if char == '{':
                brace_stack.append((line_num, i))
            elif char == '}':
                if not brace_stack:
                    print(f"Line {line_num}: Unexpected closing brace at position {i}")
                    print(f"  {line.rstrip()}")
                    print(f"  {' ' * i}^")
                else:
                    brace_stack.pop()
            
            # Track parentheses
            if char == '(':
                paren_stack.append((line_num, i))
            elif char == ')':
                if not paren_stack:
                    print(f"Line {line_num}: Unexpected closing parenthesis at position {i}")
                else:
                    paren_stack.pop()
            
            i += 1
    
    # Check for unclosed braces
    if brace_stack:
        for line_num, pos in brace_stack:
            print(f"Line {line_num}: Unclosed opening brace at position {pos}")
    
    # Check for unclosed parentheses
    if paren_stack:
        for line_num, pos in paren_stack:
            print(f"Line {line_num}: Unclosed opening parenthesis at position {pos}")
    
    # Check for problematic patterns
    print("\nChecking for problematic patterns...")
    for line_num, line in enumerate(lines, 1):
        # Check for backticks in strings
        if '`' in line:
            print(f"Line {line_num}: Contains backtick: {line.rstrip()}")
        
        # Check for .\ pattern that might cause issues
        if '".\\\\' in line or "'.\\\\" in line:
            print(f"Line {line_num}: Contains backslash in string: {line.rstrip()}")

check_powershell_syntax('docker-start.ps1')