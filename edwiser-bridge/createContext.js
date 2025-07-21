#!/usr/bin/env node

/**
 * AI Context Optimization : Comprehensive Context Creation Tool
 * ------------------------------
 * A tool for generating comprehensive code context for AI assistants while minimizing token costs.
 *
 * This script creates a single markdown file containing your entire codebase structure and content,
 * which can be pasted directly into AI chat interfaces (like Cursor's MAX models) to provide
 * complete project context in one go, eliminating the need for multiple expensive tool calls.
 *
 * BENEFITS:
 * - Save money: Reduces cost significantly when using MAX models (avoiding per file read)
 * - Better context: Provides the AI with a complete view of your project at once
 * - Privacy control: Only includes files you want, respecting custom exclusions
 * - Performance stats: Shows token counts, model compatibility, and cost estimations
 *
 * USAGE:
 * 1. Configure the excludePaths, includePaths, and includeExtensions in the config object below
 * 2. Run: node createContext.js
 * 3. Copy the generated context.md into your AI chat
 * 4. Use a custom mode with just grep and edit tools enabled (see README)
 *
 * Created by: Ghazi Khan (mgks.dev)
 * Version: 0.2.1
 *
 * CHANGELOG:
 * - v0.2.1: (2025-04-22)
 *   - Revised `find` command logic to previous with 0.2.0 improvements, it basically made the script useless.
* - v0.2.0: (2025-04-22)
 *   - Updated model context window sizes based on latest available information.
 *   - Re-included Claude 3.7 Sonnet/MAX names as requested.
 *   - Fixed recursive exclusion using improved `find` command logic.
 *   - Removed redundant helper functions (`shouldExclude`, `shouldInclude`).
 *   - Updated model context limits and warning threshold (70%).
 *   - Enhanced configuration defaults (more exclusions/extensions).
 *   - Improved statistics reporting and cost comparison.
 *   - Added debug flag, better error handling, and code quality improvements.
 *   - Refined language detection and token estimation.
 *   - Added project name and timestamp to output.
 * - v0.1.0: Initial release.
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

// Define the output file name outside the config object so we can reference it
const outputFileName = 'context.md';

// CONFIGURATION - Edit these variables as needed
const config = {
  // Output file name
  outputFile: outputFileName,

  // Directories and files to include (empty array means include everything not excluded)
  // Uses simple path matching relative to root. Applied *after* path exclusions but *before* extension filtering.
  includePaths: [],

  // Directories, files, or simple patterns (*.ext) to exclude recursively.
  excludePaths: [
    'createContext.js', outputFileName, '.DS_Store', 'node_modules', '.git', '.hg', '.svn',
    'dist', 'build', 'vendor', '.gitignore', '.env', '*.log', '*.lock',
    // Add project-specific exclusions
  ],

  // File extensions to include. Files NOT matching these will be completely ignored (not in tree or content).
  // Empty array means include all files found after path exclusions.
  includeExtensions: [
    '.js', '.jsx', '.ts', '.tsx', '.php', '.html', '.css', '.scss',
    '.json', '.md', '.txt', '.yml', '.yaml', '.config', '.py',
    // Add other extensions you want included
  ],

  // Maximum file size to include in content (in KB, to prevent massive files)
  maxFileSizeKB: 500,

  // Set to true to see the generated `find` command
  debug: false
};

// Generate the directory tree using find command with improved recursive exclusions
function generateDirectoryTree() {
  try {
    const excludePatterns = config.excludePaths.map(p => {
        const escapedPath = p.replace(/([*?"'\[\]{}() ])/g, '\\$1');
        if (escapedPath.includes('/')) {
             const cleanedPath = escapedPath.startsWith('./') ? escapedPath : `./${escapedPath}`;
            return `-path "${cleanedPath}" -o -path "${cleanedPath}/*"`;
        } else if (escapedPath.startsWith('*.')) {
             return `-name "${escapedPath}"`;
        } else if (escapedPath.includes('.')) {
             return `-name "${escapedPath}"`;
        } else {
            return `-name "${escapedPath}" -o -path "*/${escapedPath}/*"`;
        }
    }).filter(Boolean);

    let findCommand = `find . -type f`;
    if (excludePatterns.length > 0) {
      findCommand += ` -not \\( ${excludePatterns.join(' -o ')} \\)`;
    }

    // Apply includePaths filtering via find if specified
    if (config.includePaths.length > 0) {
        const includeArgs = config.includePaths.map(dir => {
             const cleanedPath = dir.startsWith('./') ? dir : `./${dir}`;
             // Use -path for include patterns - match files *within* these paths
             return `-path "${cleanedPath}/*"`; // Changed to match files *inside* the path
        }).join(' -o ');
         // Add check for files directly matching includePath if it's a file path itself
         const includeFilesArgs = config.includePaths.map(dir => {
              if (!dir.includes('*') && fs.existsSync(dir) && fs.statSync(dir).isFile()) {
                   const cleanedPath = dir.startsWith('./') ? dir : `./${dir}`;
                   return `-path "${cleanedPath}"`;
              }
              return null;
         }).filter(Boolean).join(' -o ');

         const finalIncludeArgs = includeFilesArgs ? `(${includeArgs}) -o (${includeFilesArgs})` : `(${includeArgs})`;

        findCommand += ` \\( ${finalIncludeArgs} \\)`;
    }

    findCommand += ` | sort`;

    if (config.debug) {
        console.log("DEBUG: Running find command:");
        console.log(findCommand);
    }

    const output = execSync(findCommand, { encoding: 'utf8', maxBuffer: 10 * 1024 * 1024 });
    return output.split('\n').filter(line => line.trim() !== '');

  } catch (error) {
    console.error(`\n❌ Error generating directory tree with find command.`);
    if (config.debug) { console.error(`Command attempted: ${error.cmd || findCommand}`); }
     if (error.stderr) { console.error('Stderr:', error.stderr); }
    console.error('Error:', error.message);
    return [];
  }
}


// Helper function to check if file should be included based on extension
// This is now used for the primary filtering step AFTER find returns results
function shouldIncludeBasedOnExtension(filePath) {
  // If includeExtensions is empty, always include (matches original intent)
  if (config.includeExtensions.length === 0) return true;

  const ext = path.extname(filePath).toLowerCase();
  return config.includeExtensions.includes(ext);
}

// Helper function to get file size in KB
function getFileSizeInKB(filePath) {
  try {
    const stats = fs.statSync(filePath);
    return stats.size / 1024;
  } catch (error) {
    return 0;
  }
}

// Helper function to estimate token count based on text length
function estimateTokenCount(text, fileExt = '') {
  const TOKENS_PER_CHAR = {
    '.js': 0.25, '.jsx': 0.25, '.ts': 0.25, '.tsx': 0.25, '.php': 0.25,
    '.html': 0.25, '.css': 0.22, '.scss': 0.22, '.json': 0.3, '.md': 0.18,
    '.txt': 0.18, '.yml': 0.25, '.yaml': 0.25, '.py': 0.25, '.env': 0.25,
    'default': 0.25
  };
  let tokensPerChar = TOKENS_PER_CHAR.default;
  if (fileExt && TOKENS_PER_CHAR[fileExt]) {
    tokensPerChar = TOKENS_PER_CHAR[fileExt];
  }
  return Math.ceil(text.length * tokensPerChar);
}

// Helper function to determine language from file extension for markdown code blocks
function getLanguageFromExt(filePath) {
  const ext = path.extname(filePath).toLowerCase();
  const langMap = {
    '.js': 'javascript', '.jsx': 'jsx', '.ts': 'typescript', '.tsx': 'tsx',
    '.php': 'php', '.html': 'html', '.css': 'css', '.scss': 'scss',
    '.json': 'json', '.md': 'markdown', '.txt': 'text', '.yml': 'yaml',
    '.yaml': 'yaml', '.sh': 'bash', '.bash': 'bash', '.py': 'python',
    '.java': 'java', '.c': 'c', '.cpp': 'cpp', '.cs': 'csharp', '.rb': 'ruby',
    '.go': 'go', '.rs': 'rust', '.swift': 'swift', '.kt': 'kotlin',
    '.dart': 'dart', '.sql': 'sql', '.env': 'dotenv', '.config': 'plaintext',
  };
  return langMap[ext] || 'plaintext';
}


// Generate a visual tree structure from file paths
function generateTreeStructure(files) {
  const cleanPaths = files.map(file => file.startsWith('./') ? file.substring(2) : file);
  const tree = {};
  for (const file of cleanPaths) {
    const parts = file.split('/');
    let current = tree;
    for (let i = 0; i < parts.length; i++) {
      const part = parts[i];
      const isFile = i === parts.length - 1;
      if (isFile) {
        if (!current.files) current.files = [];
        current.files.push(part);
      } else {
        if (!current.dirs) current.dirs = {};
        if (!current.dirs[part]) current.dirs[part] = {};
        current = current.dirs[part];
      }
    }
  }
  function printTree(node, prefix = '', isLast = true, path = '') {
    let result = '';
    if (node.dirs) {
      const dirs = Object.keys(node.dirs).sort();
      dirs.forEach((dir, index) => {
        const isLastDir = index === dirs.length - 1 && (!node.files || node.files.length === 0);
        result += `${prefix}${isLast && isLastDir ? '└── ' : '├── '}📁 ${dir}/\n`;
        result += printTree(node.dirs[dir], `${prefix}${isLast && isLastDir ? '    ' : '│   '}`, isLastDir);
      });
    }
    if (node.files) {
      node.files.sort();
      node.files.forEach((file, index) => {
        const isLastFile = index === node.files.length - 1;
        result += `${prefix}${isLastFile ? '└── ' : '├── '}📄 ${file}\n`;
      });
    }
    return result;
  }
  return printTree(tree);
}

// Clean file path (remove ./ prefix)
function cleanPath(filePath) {
  return filePath.startsWith('./') ? filePath.substring(2) : filePath;
}

// Format a file size for display
function formatFileSize(sizeInKB) {
  if (sizeInKB < 1024) {
    return `${sizeInKB.toFixed(2)} KB`;
  } else {
    return `${(sizeInKB / 1024).toFixed(2)} MB`;
  }
}

// Format number with commas for readability
function formatNumber(num) {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// *** MODIFIED FUNCTION ***
// Generate the context.md file
function generateContextFile() {
  console.log("🔍 Finding relevant files (excluding paths)...");
  // Step 1: Get all files matching path exclusions/inclusions
  const initialFiles = generateDirectoryTree();

  // Step 2: Filter this list based on included extensions
  console.log(`   Found ${initialFiles.length} files initially. Filtering by extension...`);
  const filesToProcess = initialFiles.filter(filePath => shouldIncludeBasedOnExtension(filePath));

  if (filesToProcess.length === 0) {
      console.log("\n⚠️ No files found matching the path *and* extension criteria. Check your excludePaths and includeExtensions in the config.");
      return;
  }
  console.log(`   ${filesToProcess.length} files match extension criteria. Processing...`);

  // Step 3: Use the final 'filesToProcess' list for everything below

  const stats = {
    totalFilesFoundInitial: initialFiles.length, // How many find returned
    totalFilesProcessed: filesToProcess.length, // How many we are using
    includedFileContents: 0,
    skippedFileContents: 0, // Will now only be for size limit
    skippedDueToSize: 0,
    // skippedDueToType is no longer applicable here
    totalTokens: 0,
    totalCharacters: 0,
    totalOriginalSizeKB: 0, // Size of files *processed*
    filesByType: {},        // Types of files *processed*
    tokensPerFileType: {}   // Tokens for files *processed*
  };

  const projectName = path.basename(process.cwd());
  let outputContent = `# Project Context: ${projectName}\n\nGenerated: ${new Date().toISOString()}\n\n`;

  console.log("🌳 Generating directory structure...");
  outputContent += `## Directory Structure\n\n\`\`\`\n`;
  // Generate tree ONLY from the final filtered list
  outputContent += generateTreeStructure(filesToProcess);
  outputContent += `\`\`\`\n\n`;

  console.log("📄 Processing file contents...");
  outputContent += `## File Contents\n\n`;

  // Iterate ONLY over the final filtered list
  for (const filePath of filesToProcess) {
    const cleanFilePath = filePath; // Path is already like ./src/file.js
    const fileExt = path.extname(filePath).toLowerCase();

    // Track stats only for processed files
    const currentFileSizeKB = getFileSizeInKB(filePath);
    stats.totalOriginalSizeKB += currentFileSizeKB;
    if (!stats.filesByType[fileExt]) {
      stats.filesByType[fileExt] = 0;
      stats.tokensPerFileType[fileExt] = 0;
    }
    stats.filesByType[fileExt]++;

    // *** The check for shouldIncludeBasedOnExtension is ALREADY DONE ***
    // We only need to check for size limit now

    if (currentFileSizeKB > config.maxFileSizeKB) {
      stats.skippedFileContents++;
      stats.skippedDueToSize++;
      // Still mention the file, but only note the size skip reason
      outputContent += `### \`${cleanFilePath}\`\n\n*File content skipped: Size ${formatFileSize(currentFileSizeKB)} exceeds ${config.maxFileSizeKB} KB limit.*\n\n`;
      continue;
    }

    // Include content (file passed extension and size checks)
    try {
      const fileContent = fs.readFileSync(filePath, 'utf8');
      const language = getLanguageFromExt(filePath);

      stats.includedFileContents++;
      const fileTokens = estimateTokenCount(fileContent, fileExt);
      stats.totalTokens += fileTokens;
      stats.totalCharacters += fileContent.length;
      stats.tokensPerFileType[fileExt] += fileTokens;

      outputContent += `### \`${cleanFilePath}\`\n\n`;
      outputContent += `\`\`\`${language}\n`;
      outputContent += fileContent.trim() ? fileContent : '[EMPTY FILE]';
      outputContent += `\n\`\`\`\n\n`;
    } catch (error) {
      // This file *should* have been included, but reading failed
      stats.skippedFileContents++; // Count as skipped due to error
      outputContent += `### \`${cleanFilePath}\`\n\n*Error reading file: ${error.message}*\n\n`;
       console.warn(`Warning: Could not read file ${cleanFilePath}: ${error.message}`);
    }
  }

  const structureOnlyContent = outputContent.replace(/```[\s\S]*?```/g, '');
  const structureTokens = estimateTokenCount(structureOnlyContent, '.md');
  stats.totalTokens += structureTokens;

  fs.writeFileSync(config.outputFile, outputContent);
  console.log(`💾 Writing output to ${config.outputFile}...`);

  const outputFileSizeKB = getFileSizeInKB(config.outputFile);

  console.log("\n" + "=".repeat(60));
  console.log(`📊 CONTEXT FILE STATISTICS`);
  console.log("=".repeat(60));
  console.log(`📝 Content Summary:`);
  console.log(`  • Context file created: ${config.outputFile}`);
  console.log(`  • File size: ${formatFileSize(outputFileSizeKB)}`);
  console.log(`  • Estimated tokens: ~${formatNumber(stats.totalTokens)}`);
  console.log(`  • Characters (included content): ${formatNumber(stats.totalCharacters)}`);
  console.log(`  • Markdown overhead: ~${formatNumber(structureTokens)} tokens`);

  console.log(`\n📁 File Processing:`);
  console.log(`  • Files found by path search: ${stats.totalFilesFoundInitial}`);
  console.log(`  • Files included (matching extensions): ${stats.totalFilesProcessed}`);
  console.log(`  • File content included in output: ${stats.includedFileContents}`);
  console.log(`  • File content skipped (size limit): ${stats.skippedDueToSize}`);
  // Removed skipped by type line
  console.log(`  • Total size of included files: ${formatFileSize(stats.totalOriginalSizeKB)}`);


  console.log(`\n📊 File Types Distribution (Included Files):`);
  const sortedFileTypes = Object.entries(stats.tokensPerFileType)
    .filter(([, tokens]) => tokens > 0)
    .sort((a, b) => b[1] - a[1])
    .slice(0, 8);

  sortedFileTypes.forEach(([ext, tokens]) => {
    const totalFoundOfType = stats.filesByType[ext] || 0; // Count of this type among processed files
    console.log(`  • ${ext || '(no ext)'}: ~${formatNumber(tokens)} tokens (${totalFoundOfType} files)`);
  });


  console.log(`\n📈 Token Usage by AI Model:`);
  const models = [
    { name: "Claude 3 Haiku", limit: 200000 }, { name: "Claude 3.5 Sonnet", limit: 200000 },
    { name: "GPT-4o", limit: 128000 }, { name: "GPT-4 Turbo", limit: 128000 },
    { name: "Gemini 1.5 Pro", limit: 1000000 }, { name: "Gemini 1.5 Flash", limit: 1000000 },
  ];
  models.forEach(model => {
    const percentUsed = Math.min(100, (stats.totalTokens / model.limit) * 100);
    const statusSymbol = stats.totalTokens > model.limit ? "❌" : percentUsed > 70 ? "⚠️" : "✅";
    console.log(`  ${statusSymbol} ${model.name}: ${percentUsed.toFixed(1)}% used (~${formatNumber(stats.totalTokens)} / ${formatNumber(model.limit)} tokens)`);
  });

  console.log("\n💰 Cost Comparison (Illustrative):");
  const costs = [
    { name: "Claude 3.5 Sonnet ($3/M)", cost: (stats.totalTokens / 1000000) * 3 },
    { name: "GPT-4o ($5/M)", cost: (stats.totalTokens / 1000000) * 5 },
    { name: "Gemini 1.5 Pro ($3.5/M)", cost: (stats.totalTokens / 1000000) * 3.5 },
    { name: "Cursor MAX (This Script)", cost: 0.05 },
    { name: "Cursor MAX (File Reads)", cost: Math.max(0.05, 0.05 * stats.includedFileContents) },
  ];
  costs.forEach(model => { console.log(`  • ${model.name}: ~$${model.cost.toFixed(4)}`); });

  const toolCallCost = Math.max(0.05, 0.05 * stats.includedFileContents);
  const scriptCost = 0.05;
  if (toolCallCost > scriptCost && stats.includedFileContents > 1) {
      const savingsAmount = toolCallCost - scriptCost;
      const savingsPercent = toolCallCost > 0 ? (savingsAmount / toolCallCost) * 100 : 0;
      console.log(`\n💸 Potential Tool Call Savings (Example @ $0.05/call):`);
      console.log(`  • Est. Cost w/ File Reads: ~$${toolCallCost.toFixed(2)} (${stats.includedFileContents} included files)`);
      console.log(`  • Est. Cost w/ This Script: ~$${scriptCost.toFixed(2)} (1 prompt)`);
      console.log(`  • Potential Savings: ~$${savingsAmount.toFixed(2)} (${savingsPercent.toFixed(0)}%)`);
  }

  console.log("=".repeat(60));
  console.log(`✨ Done! Copy the contents of ${config.outputFile} into your AI chat.`);
  console.log("=".repeat(60) + "\n");
}

// Execute the main function
generateContextFile();